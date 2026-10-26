<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/request_policy.php';
$checks = 0;
$check = static function(bool $ok) use (&$checks): void {
    if (!$ok) throw new RuntimeException('Request policy regression at check ' . ($checks + 1));
    $checks++;
};
$token = str_repeat('a', 64);
$user = ['authenticated'=>true,'user_id'=>1,'current_project'=>10,'user_role'=>'user','csrf_token'=>$token];
$viewer = array_replace($user, ['user_role'=>'viewer']);
$admin = array_replace($user, ['user_role'=>'admin']);
foreach (['ajax_activities.php'=>'list','ajax_notes.php'=>'get','ajax_attendees.php'=>'list','ajax_subcontractors.php'=>'list','ajax_resource_stats.php'=>''] as $file=>$action) {
    $check(dabsRequestStatus($file,'GET',['action'=>$action],[],[]) === 401);
    $check(dabsRequestStatus($file,'GET',['action'=>$action],[],$viewer) === 200);
    $check(dabsRequestStatus($file,'GET',['action'=>$action,'project_id'=>99],[],$viewer) === 403);
}
foreach (['ajax_activities.php'=>'add','ajax_notes.php'=>'save','ajax_safety.php'=>'update','ajax_attendees.php'=>'delete','ajax_subcontractors.php'=>'update'] as $file=>$action) {
    $check(dabsRequestStatus($file,'POST',[],['action'=>$action],$viewer,$token) === 403);
    $check(dabsRequestStatus($file,'GET',['action'=>$action],[],$user) === 405);
    $check(dabsRequestStatus($file,'POST',[],['action'=>$action],$user) === 403);
    $check(dabsRequestStatus($file,'POST',[],['action'=>$action],$user,str_repeat('b',64)) === 403);
    $check(dabsRequestStatus($file,'POST',[],['action'=>$action],$user,$token) === 200);
}
$check(dabsRequestStatus('unknown.php','GET',[],[],$admin) === 400);
$check(dabsRequestStatus('ajax_activities.php','POST',[],['action'=>['add']],$admin,$token) === 400);
$check(dabsRequestStatus('ajax_admin.php','GET',['action'=>'list_users'],[],$user) === 403);
$check(dabsRequestStatus('ajax_admin.php','GET',['action'=>'delete_user'],[],$admin) === 405);
$check(dabsRequestStatus('ajax_admin.php','POST',[],['action'=>'delete_user'],$admin,$token) === 200);
$check(dabsRequestStatus('email_briefing.php','POST',[],[],$user,$token) === 403);
$check(dabsRequestStatus('email_briefing.php','POST',[],[],$admin,$token) === 200);
$check(dabsRequestStatus('ajax_notes.php','POST',[],['action'=>'save','csrf_token'=>$token],$user) === 200);
$check(!dabsPermissionAllowed(array_replace($user,['user_role'=>'unknown']),'read'));
$check(!dabsPermissionAllowed($admin,'unknown'));
$check(!dabsPermissionAllowed(array_replace($user,['current_project'=>0]),'read'));
echo json_encode(['ok'=>true,'checks'=>$checks,'live_database_access'=>false,'tenant_isolation_verified'=>false]) . PHP_EOL;
