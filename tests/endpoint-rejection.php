<?php
declare(strict_types=1);
/** Execute real entry points; rejection must happen before database/vendor loading. */
$checks = 0;
$root = dirname(__DIR__);
$endpoints = ['ajax_activities.php','ajax_notes.php','ajax_safety.php','ajax_attendees.php','ajax_subcontractors.php','ajax_branding.php','ajax_admin.php','ajax_resource_stats.php','email_briefing.php'];
$run = static function(string $endpoint, array $session, array $post, int $expected) use ($root, &$checks): void {
    $code = 'session_start(); $_SESSION=' . var_export($session,true) . '; $_GET=[]; $_POST=' . var_export($post,true)
        . '; $_SERVER["REQUEST_METHOD"]="POST"; register_shutdown_function(static function(){fwrite(STDERR,(string)http_response_code());}); require '
        . var_export($root . '/' . $endpoint,true) . ';';
    $process = proc_open([PHP_BINARY,'-d','display_errors=0','-r',$code], [1=>['pipe','w'],2=>['pipe','w']], $pipes, $root);
    if (!is_resource($process)) throw new RuntimeException('Cannot start endpoint check.');
    $output = stream_get_contents($pipes[1]); $status = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]); $exit = proc_close($process);
    $reply = json_decode($output,true);
    if ($exit !== 0 || trim($status) !== (string)$expected || ($reply['ok'] ?? null) !== false) {
        throw new RuntimeException('Endpoint rejection failed: ' . $endpoint);
    }
    $checks++;
};
foreach ($endpoints as $endpoint) $run($endpoint,[],[],401);
$viewer = ['authenticated'=>true,'user_id'=>1,'current_project'=>10,'user_role'=>'viewer','csrf_token'=>str_repeat('a',64)];
foreach (['ajax_activities.php'=>'delete','ajax_notes.php'=>'save','ajax_safety.php'=>'update','ajax_attendees.php'=>'add','ajax_subcontractors.php'=>'update','ajax_branding.php'=>'save','ajax_admin.php'=>'create_user','email_briefing.php'=>''] as $file=>$action) {
    $run($file,$viewer,['action'=>$action],403);
}
echo json_encode(['ok'=>true,'checks'=>$checks,'live_database_access'=>false]) . PHP_EOL;
