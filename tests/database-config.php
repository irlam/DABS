<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/database_config.php';
$temp = sys_get_temp_dir() . '/dabs-config-test-' . bin2hex(random_bytes(8));
mkdir($temp, 0700);
$keys = ['DABS_DATABASE_CONFIG','DABS_DB_HOST','DABS_DB_PORT','DABS_DB_NAME','DABS_DB_USER','DABS_DB_PASSWORD'];
foreach ($keys as $key) putenv($key);
putenv('DABS_DATABASE_CONFIG=' . $temp . '/missing.php');
$checks = 0;
function check(bool $condition): void { global $checks; if (!$condition) throw new RuntimeException('Configuration regression'); $checks++; }
function rejects(callable $callback): void { try { $callback(); } catch (RuntimeException $e) { check(true); return; } throw new RuntimeException('Expected configuration rejection'); }
try {
    rejects(fn() => dabsDatabaseConfig());
    $file = $temp . '/database.php';
    $values = ['host'=>'localhost','name'=>'carefoot_test','user'=>'test_user','password'=>'synthetic-only'];
    file_put_contents($file, '<?php return ' . var_export($values, true) . ';'); chmod($file, 0600);
    putenv('DABS_DATABASE_CONFIG=' . $file);
    $actual = dabsDatabaseConfig(); check($actual['name'] === 'carefoot_test'); check($actual['port'] === 3306);
    putenv('DABS_DB_NAME=isolated_project'); check(dabsDatabaseConfig()['name'] === 'isolated_project');
    putenv('DABS_DB_HOST=localhost;dbname=other'); rejects(fn() => dabsDatabaseConfig()); putenv('DABS_DB_HOST');
    putenv('DABS_DB_PORT=0'); rejects(fn() => dabsDatabaseConfig()); putenv('DABS_DB_PORT');
    putenv('DABS_DB_PASSWORD='); rejects(fn() => dabsDatabaseConfig()); putenv('DABS_DB_PASSWORD');
    chmod($file, 0644); clearstatcache(); rejects(fn() => dabsDatabaseConfig()); chmod($file, 0600);
    symlink($file, $temp . '/link.php'); putenv('DABS_DATABASE_CONFIG=' . $temp . '/link.php'); rejects(fn() => dabsDatabaseConfig());
    putenv('DABS_DATABASE_CONFIG=' . __FILE__); rejects(fn() => dabsDatabaseConfig());
    putenv('DABS_DATABASE_CONFIG=' . $temp . '/missing.php');
    foreach (['HOST'=>'localhost','NAME'=>'env_test','USER'=>'test_user','PASSWORD'=>'synthetic-only'] as $key=>$value) putenv('DABS_DB_' . $key . '=' . $value);
    check(dabsDatabaseConfig()['name'] === 'env_test');
    echo json_encode(['ok'=>true,'checks'=>$checks,'live_database_access'=>false,'secrets_printed'=>false]) . "\n";
} finally {
    foreach ($keys as $key) putenv($key);
    @unlink($temp . '/link.php'); @unlink($temp . '/database.php'); @rmdir($temp);
}
