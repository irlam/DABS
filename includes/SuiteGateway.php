<?php
declare(strict_types=1);

/** Dedicated DABS instance. Authority always comes from the current Suite session. */
final class DabsSuiteGateway
{
    public static function configuration(string $root): array
    {
        $root = realpath($root);
        if (!$root) throw new RuntimeException('Invalid application root.');
        $path = getenv('DABS_SUITE_CONFIG') ?: dirname($root) . '/private/dabs-suite.php';
        $real = realpath($path);
        if (!$real || is_link($path) || !is_file($real) || !is_readable($real)
            || $real === $root || str_starts_with($real, $root . '/')
            || (fileperms($real) & 0777) !== 0600
            || (fileperms(dirname($real)) & 0777) !== 0700 || is_link(dirname($path))) {
            throw new RuntimeException('Private Suite binding unavailable.');
        }
        $b = (static fn(string $f) => require $f)($real);
        if (!is_array($b)) throw new RuntimeException('Invalid Suite binding.');
        self::binding($b);
        $sessions = $b['session_path'] ?? '';
        if (!is_string($sessions) || !is_dir($sessions) || is_link($sessions)
            || realpath($sessions) !== $sessions || (fileperms($sessions) & 0777) !== 0700
            || $sessions === $root || str_starts_with($sessions, $root . '/')) {
            throw new RuntimeException('Private session storage unavailable.');
        }
        return $b;
    }

    public static function binding(array $b): void
    {
        foreach (['instance_id','organization_id','project_id','local_project_id'] as $field) {
            if (!is_int($b[$field] ?? null) || $b[$field] < 1) throw new RuntimeException('Invalid binding identity.');
        }
        if (($b['module_key'] ?? '') !== 'dabs' || !preg_match('/^[a-f0-9]{64}$/D', (string)($b['key'] ?? ''))) {
            throw new RuntimeException('Invalid binding key or module.');
        }
        $url = $b['origin'] ?? '';
        $host = is_string($url) ? parse_url($url, PHP_URL_HOST) : null;
        if (!is_string($host) || !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
            || !str_ends_with($host, '.dabs.defecttracker.uk') || $url !== 'https://' . $host) {
            throw new RuntimeException('A dedicated DABS HTTPS origin is required.');
        }
    }

    public static function identity(array $b, array $reply, ?int $now = null): array
    {
        $now ??= time();
        $i = $reply['identity'] ?? null;
        if (($reply['ok'] ?? false) !== true || !is_array($i)) throw new RuntimeException('Invalid Suite reply.');
        foreach (['instance_id','organization_id','project_id','module_key'] as $field) {
            if (($i[$field] ?? null) !== $b[$field]) throw new RuntimeException('Wrong Suite audience.');
        }
        if (!is_int($i['user_id'] ?? null) || $i['user_id'] < 1
            || !is_int($i['session_expires_at'] ?? null) || $i['session_expires_at'] <= $now
            || !is_string($i['name'] ?? null) || trim($i['name']) === '' || strlen($i['name']) > 180
            || !in_array($i['role'] ?? '', ['platform_admin','admin','manager','site_manager','user','contractor','viewer'], true)) {
            throw new RuntimeException('Invalid Suite identity.');
        }
        return $i;
    }

    public static function localRole(string $role): string
    {
        return match ($role) {
            'platform_admin','admin' => 'admin',
            'manager','site_manager' => 'manager',
            'user' => 'user',
            'contractor','viewer' => 'viewer',
            default => throw new RuntimeException('Unsupported role.'),
        };
    }

    public static function database(PDO $db, array $b): void
    {
        $row = $db->query('SELECT instance_id,organization_id,project_id,local_project_id,module_key FROM suite_deployment WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new RuntimeException('Missing database binding.');
        foreach (['instance_id','organization_id','project_id','local_project_id'] as $field) {
            if ((int)$row[$field] !== $b[$field]) throw new RuntimeException('Database audience mismatch.');
        }
        if ($row['module_key'] !== 'dabs') throw new RuntimeException('Wrong database module.');
        $ids = $db->query('SELECT id FROM projects')->fetchAll(PDO::FETCH_COLUMN);
        if (count($ids) !== 1 || (int)$ids[0] !== $b['local_project_id']) throw new RuntimeException('Database must contain only the bound project.');
    }

    public static function request(array $b, string $endpoint, array $payload, ?callable $transport = null): array
    {
        if (!in_array($endpoint, ['module-handoff','module-session'], true)) throw new RuntimeException('Unsupported Suite endpoint.');
        $payload['instance_id'] = $b['instance_id'];
        if ($transport) $reply = $transport($endpoint, $payload);
        else {
            $body = '';
            $ch = curl_init('https://suite.defecttracker.uk/api/v1/' . $endpoint . '.php');
            curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload,JSON_THROW_ON_ERROR),
                CURLOPT_FOLLOWLOCATION=>false,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
                CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>8,
                CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Construction-Suite-Key: ' . $b['key']],
                CURLOPT_WRITEFUNCTION=>static function($curl,$chunk) use (&$body) {
                    if (strlen($body)+strlen($chunk)>65536) return 0;
                    $body.=$chunk; return strlen($chunk);
                }]);
            try { $ok=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); }
            finally { curl_close($ch); }
            if ($ok === false || $status !== 200) throw new RuntimeException('Suite validation unavailable.');
            $reply=json_decode($body,true,16,JSON_THROW_ON_ERROR);
        }
        if (!is_array($reply) || ($reply['ok'] ?? false) !== true) throw new RuntimeException('Suite validation failed.');
        return $reply;
    }

    public static function pending(array $session, array $post, int $now): array
    {
        $p=$session['dabs_pending']??null;
        $code=$post['code']??null; $state=$post['state']??null;
        if (!is_array($p) || !is_int($p['expires']??null) || $p['expires']<=$now
            || !is_string($code) || !preg_match('/^[a-f0-9]{64}$/D',$code)
            || !is_string($state) || !preg_match('/^[a-f0-9]{64}$/D',$state)
            || !is_string($p['state']??null) || !hash_equals($p['state'],$state)) throw new RuntimeException('Sign-in state expired.');
        return ['code'=>$code,'state'=>$state];
    }

    public static function localUser(PDO $db, array $b, array $i, bool $create = false): int
    {
        $q=$db->prepare('SELECT local_user_id,enabled FROM suite_users WHERE suite_user_id=?');
        $q->execute([$i['user_id']]); $map=$q->fetch(PDO::FETCH_ASSOC);
        if (!$map && $create) {
            $db->beginTransaction();
            try {
                $q=$db->prepare("INSERT INTO users(username,password,name,email,role) VALUES(?,?,?,?,'user')");
                $q->execute(['suite-'.$i['user_id'],password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT),
                    mb_substr($i['name'],0,100),'suite-'.$b['instance_id'].'-'.$i['user_id'].'@suite.invalid']);
                $id=(int)$db->lastInsertId();
                $q=$db->prepare('INSERT INTO suite_users(suite_user_id,local_user_id,enabled) VALUES(?,?,1)');
                $q->execute([$i['user_id'],$id]); $db->commit();
            } catch(Throwable $e) { if($db->inTransaction())$db->rollBack(); throw $e; }
            return self::localUser($db,$b,$i);
        }
        if (!$map || (int)$map['enabled'] !== 1) throw new RuntimeException('Local account disabled.');
        $q=$db->prepare('SELECT id FROM users WHERE id=?'); $q->execute([$map['local_user_id']]);
        $id=$q->fetchColumn(); if (!$id) throw new RuntimeException('Local account unavailable.');
        return (int)$id;
    }

    public static function apply(array $b, array $i, int $localUser): void
    {
        $_SESSION['authenticated']=true;
        $_SESSION['user_id']=$localUser;
        $_SESSION['user_name']=$i['name'];
        $_SESSION['user_role']=self::localRole($i['role']);
        $_SESSION['current_project']=$b['local_project_id'];
        $_SESSION['last_activity']=time();
    }

    public static function allowedPath(string $path, string $role): bool
    {
        $paths=['/','/index.php','/activity_schedule_tabs.php','/ajax_activities.php','/ajax_notes.php',
            '/ajax_safety.php','/ajax_attendees.php','/ajax_subcontractors.php','/ajax_resource_stats.php',
            '/email_briefing.php','/logout.php'];
        if ($role === 'admin') $paths=array_merge($paths,['/admin_panel.php','/ajax_branding.php']);
        // User accounts, credentials, logs and migrations are never administered in a module.
        return in_array($path,$paths,true);
    }
}
