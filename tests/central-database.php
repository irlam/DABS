<?php
declare(strict_types=1);
/** Regression: all application PDO construction must use the deployment config. */
$root = dirname(__DIR__);
$checks = 0;
foreach (['ajax_notes.php','ajax_safety.php','ajax_resource_stats.php','ajax_attendees.php','activity_schedule_tabs.php','logout.php'] as $file) {
    $source = file_get_contents($root . '/' . $file);
    if (preg_match('/new\\s+PDO\\b/i', $source)
        || !str_contains($source, "'/includes/db_connect.php'")
        || !str_contains($source, 'connectToDatabase()')) {
        throw new RuntimeException('Database configuration bypass: ' . $file);
    }
    $checks++;
}
$source = file_get_contents($root . '/includes/db_connect.php');
if (!str_contains($source, 'dabsDatabaseConfig()') || !str_contains($source, 'port=$db_port')) {
    throw new RuntimeException('Shared connection must use private config and its port.');
}
echo json_encode(['ok'=>true,'checks'=>$checks+1,'live_database_access'=>false]) . PHP_EOL;
