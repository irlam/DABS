<?php
declare(strict_types=1);
// Reproduce hosting open_basedir warnings using real HTTP, without any database.
$root=dirname(__DIR__);$temp=sys_get_temp_dir().'/dabs-http-'.bin2hex(random_bytes(6));
mkdir($temp,0700);mkdir($temp.'/web',0700);mkdir($temp.'/web/includes',0700);mkdir($temp.'/private',0700);
foreach(['suite-gate.php','includes/SuiteGateway.php'] as $file)copy($root.'/'.$file,$temp.'/web/'.$file);
file_put_contents($temp.'/web/health.php',"<?php require __DIR__.'/suite-gate.php';");
file_put_contents($temp.'/private/dabs-suite.php',"<?php return [];\n");
$socket=stream_socket_server('tcp://127.0.0.1:0',$errno,$error);
if(!$socket)throw new RuntimeException('Cannot allocate test port.');
$address=stream_socket_get_name($socket,false);fclose($socket);
$process=proc_open([PHP_BINARY,'-d','display_errors=1','-d','open_basedir='.$temp.'/web','-S',$address,'-t',$temp.'/web'],[['pipe','r'],['file',$temp.'/server.log','a'],['file',$temp.'/server.log','a']],$pipes,$temp.'/web');
try{
 if(!is_resource($process))throw new RuntimeException('Cannot start test server.');
 fclose($pipes[0]);$body=false;$headers=[];
 $context=stream_context_create(['http'=>['ignore_errors'=>true,'timeout'=>1]]);
 for($attempt=0;$attempt<30;$attempt++){
  $body=@file_get_contents('http://'.$address.'/health.php',false,$context);
  $headers=$http_response_header??[];
  if($body!==false)break;
  usleep(100000);
 }
 if(!is_string($body)||!str_contains($headers[0]??'',' 503 ')
   ||str_contains($body,'Warning')||str_contains($body,$temp)
   ||!str_contains($body,'DABS project access could not be verified'))throw new RuntimeException('Gateway did not fail closed without warnings.');
 echo json_encode(['ok'=>true,'http_status'=>503,'warning_leak'=>false,'database_used'=>false]).PHP_EOL;
}finally{
 if(is_resource($process)){proc_terminate($process);proc_close($process);}
 foreach(['web/health.php','web/suite-gate.php','web/includes/SuiteGateway.php','private/dabs-suite.php','server.log']as$file)@unlink($temp.'/'.$file);
 @rmdir($temp.'/web/includes');@rmdir($temp.'/web');@rmdir($temp.'/private');@rmdir($temp);
}
