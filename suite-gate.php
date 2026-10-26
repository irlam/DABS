<?php
declare(strict_types=1);
// Install as auto_prepend_file on the dedicated host. Also enforced by the marker
// check in db_connect.php, so a missing FPM prepend does not restore native login.
if (PHP_SAPI === 'cli' || defined('DABS_SUITE_ACTIVE')) return;
define('DABS_SUITE_ACTIVE', true);
// Hosting warnings must never commit a 200 response before a denial.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
require_once __DIR__ . '/includes/SuiteGateway.php';
$gatePhase='private_configuration';
try {
    $b=DabsSuiteGateway::configuration(__DIR__);
    $gatePhase='host';
    if (($_SERVER['HTTP_HOST']??'') !== parse_url($b['origin'],PHP_URL_HOST)
        || ($_SERVER['HTTPS']??'') !== 'on') throw new RuntimeException('Wrong host.');
    $gatePhase='session';
    if (session_status() === PHP_SESSION_ACTIVE) throw new RuntimeException('Suite gate must run before native sessions.');
    ini_set('session.use_strict_mode','1'); ini_set('session.use_only_cookies','1');
    session_save_path($b['session_path']); session_name('__Host-dabs-suite');
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
    header('Cache-Control: private, no-store'); header('Referrer-Policy: no-referrer');
    $gatePhase='database_connection';
    require_once __DIR__ . '/includes/db_connect.php';
    $db=connectToDatabase(); $gatePhase='database_binding'; DabsSuiteGateway::database($db,$b);
    $gatePhase='request';
    $path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
    if (!is_string($path) || preg_match('/[\x00-\x20\\\\]/',rawurldecode($path)) || str_contains(rawurldecode($path),'..')) throw new RuntimeException('Invalid path.');
    if ($path === '/health.php') {
        header('Content-Type: application/json'); echo json_encode(['ok'=>true,'module'=>'dabs']); exit;
    }
    if ($path === '/suite-login.php') {
        $GLOBALS['dabs_suite_binding']=$b; $GLOBALS['dabs_suite_db']=$db; return;
    }
    $auth=$_SESSION['dabs_suite']??null;
    if (!is_array($auth) || !is_string($auth['token']??null) || !preg_match('/^[a-f0-9]{64}$/D',$auth['token'])) {
        $_SESSION=[];
        if (str_starts_with($path,'/ajax_') || $path==='/email_briefing.php') {
            http_response_code(401); header('Content-Type: application/json'); echo '{"ok":false,"error":"Suite sign-in required"}'; exit;
        }
        header('Location: /suite-login.php',true,303); exit;
    }
    $gatePhase='suite_session';
    $i=DabsSuiteGateway::identity($b,DabsSuiteGateway::request($b,'module-session',['action'=>'validate','session_token'=>$auth['token']]));
    if (($auth['user_id']??null) !== $i['user_id']) throw new RuntimeException('Account changed.');
    $local=DabsSuiteGateway::localUser($db,$b,$i);
    DabsSuiteGateway::apply($b,$i,$local);
    $role=DabsSuiteGateway::localRole($i['role']);
    if (!DabsSuiteGateway::allowedPath($path,$role)) { http_response_code(403); exit('This page is unavailable for your Suite role.'); }
    if ($path === '/logout.php') {
        $expected=$_SESSION['csrf_token']??''; $supplied=$_POST['csrf_token']??'';
        if (($_SERVER['REQUEST_METHOD']??'')!=='POST' || !is_string($supplied) || !preg_match('/^[a-f0-9]{64}$/D',(string)$expected) || !hash_equals($expected,$supplied)) {
            http_response_code(403); exit('Use the project sign-out button.');
        }
        $token=$auth['token']; $_SESSION=[]; session_regenerate_id(true);
        try { DabsSuiteGateway::request($b,'module-session',['action'=>'revoke','session_token'=>$token]); } catch(Throwable $ignored) {}
        setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
        session_destroy(); header('Clear-Site-Data: "cache", "cookies", "storage"');
        header('Location: https://suite.defecttracker.uk/',true,303); exit;
    }
    if (!str_starts_with($path,'/ajax_') && $path!=='/email_briefing.php' && !in_array($_SERVER['REQUEST_METHOD']??'',['GET','HEAD'],true)) { http_response_code(405); exit; }
    $GLOBALS['dabs_suite_binding']=$b;
    ob_start(static function(string $html): string {
        if (!preg_match('/<body\b/i',$html)) return $html;
        $token=htmlspecialchars((string)($_SESSION['csrf_token']??''),ENT_QUOTES,'UTF-8');
        $bar='<div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;padding:12px;background:#0b1220;color:white"><a style="color:#52c8e8" href="https://suite.defecttracker.uk/">Construction Suite</a><span>Project controlled access</span><form method="post" action="/logout.php"><input type="hidden" name="csrf_token" value="'.$token.'"><button type="submit">Sign out of DABS</button></form></div>';
        return preg_replace('/(<body\b[^>]*>)/i','$1'.$bar,$html,1)??$html;
    });
} catch(Throwable $e) {
    error_log('[DABS Suite gate] failed phase: '.$gatePhase);
    if(session_status()===PHP_SESSION_ACTIVE)$_SESSION=[];
    http_response_code(503); header('Cache-Control: no-store');
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><p>DABS project access could not be verified. <a href="https://suite.defecttracker.uk/">Return to Construction Suite</a>.</p>'; exit;
}
