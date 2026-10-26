<?php
declare(strict_types=1);

/** Deployment configuration only. Never accepts a path or credentials from HTTP input. */
function dabsDatabaseConfig(): array
{
    $root = dirname(__DIR__);
    $path = getenv('DABS_DATABASE_CONFIG');
    $path = $path !== false && $path !== '' ? $path : dirname($root) . '/private/dabs-database.php';
    $config = [];
    if (file_exists($path) || is_link($path)) {
        $real = realpath($path);
        $web = realpath($root);
        if ($real === false || $web === false || is_link($path) || !is_file($real)
            || !is_readable($real) || $real === $web || str_starts_with($real, $web . DIRECTORY_SEPARATOR)
            || (fileperms($real) & 0077) !== 0) {
            throw new RuntimeException('DABS requires a private database configuration outside the web root with owner-only permissions.');
        }
        $config = (static fn(string $file) => require $file)($real);
        if (!is_array($config)) throw new RuntimeException('Invalid DABS database configuration.');
    }
    $env = ['host' => 'DABS_DB_HOST', 'port' => 'DABS_DB_PORT', 'name' => 'DABS_DB_NAME',
        'user' => 'DABS_DB_USER', 'password' => 'DABS_DB_PASSWORD'];
    foreach ($env as $key => $name) {
        $value = getenv($name);
        if ($value !== false) $config[$key] = $value;
    }
    foreach (['host', 'name', 'user', 'password'] as $key) {
        if (!isset($config[$key]) || !is_string($config[$key]) || $config[$key] === '') {
            throw new RuntimeException('DABS database configuration is incomplete.');
        }
    }
    if (!preg_match('/^[a-zA-Z0-9._:-]+$/D', $config['host'])
        || !preg_match('/^[a-zA-Z0-9_]+$/D', $config['name'])) {
        throw new RuntimeException('Invalid DABS database configuration.');
    }
    $port = filter_var($config['port'] ?? 3306, FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 65535]]);
    if ($port === false) throw new RuntimeException('Invalid DABS database port.');
    return ['host' => $config['host'], 'port' => $port, 'name' => $config['name'],
        'user' => $config['user'], 'password' => $config['password']];
}
