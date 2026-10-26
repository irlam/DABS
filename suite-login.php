<?php
declare(strict_types=1);
require_once __DIR__ . '/suite-gate.php';
$b=$GLOBALS['dabs_suite_binding']; $db=$GLOBALS['dabs_suite_db']; $token=null;
try {
    if (($_SERVER['REQUEST_METHOD']??'')==='GET') {
        $old=$_SESSION['dabs_suite']['token']??null;
        $_SESSION=[];session_regenerate_id(true);
        if(is_string($old))try{DabsSuiteGateway::request($b,'module-session',['action'=>'revoke','session_token'=>$old]);}catch(Throwable $ignored){}
        $_SESSION['dabs_pending']=['state'=>bin2hex(random_bytes(32)),'expires'=>time()+300];
        setcookie(session_name(),session_id(),['path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'None']);
        header('Location: https://suite.defecttracker.uk/launch.php?instance_id='.$b['instance_id'].'&state='.$_SESSION['dabs_pending']['state'],true,303);exit;
    }
    if (($_SERVER['REQUEST_METHOD']??'')!=='POST') throw new RuntimeException('Method denied.');
    $session=$_SESSION; unset($_SESSION['dabs_pending']);
    $grant=DabsSuiteGateway::pending($session,$_POST,time());
    $i=DabsSuiteGateway::identity($b,DabsSuiteGateway::request($b,'module-handoff',$grant));
    $token=$i['session_token']??null;
    if(!is_string($token)||!preg_match('/^[a-f0-9]{64}$/D',$token))throw new RuntimeException('Invalid session.');
    $i=DabsSuiteGateway::identity($b,DabsSuiteGateway::request($b,'module-session',['action'=>'validate','session_token'=>$token]));
    $local=DabsSuiteGateway::localUser($db,$b,$i,true);
    session_regenerate_id(true);
    $_SESSION=['dabs_suite'=>['token'=>$token,'user_id'=>$i['user_id']],'csrf_token'=>bin2hex(random_bytes(32))];
    DabsSuiteGateway::apply($b,$i,$local);
    setcookie(session_name(),session_id(),['path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    header('Location: /index.php',true,303);exit;
} catch(Throwable $e) {
    $_SESSION=[];
    if(is_string($token))try{DabsSuiteGateway::request($b,'module-session',['action'=>'revoke','session_token'=>$token]);}catch(Throwable $ignored){}
    http_response_code(401);header('Cache-Control: no-store');
    echo '<!doctype html><meta charset="utf-8"><p>Sign-in could not be completed. <a href="https://suite.defecttracker.uk/">Return to Suite</a>.</p>';
}
