<?php
declare(strict_types=1);
/** Disposable CI database only. Never accepts production connection arguments. */
if(getenv('DABS_CI_DATABASE')!=='1')throw new RuntimeException('CI database opt-in required.');
require dirname(__DIR__).'/includes/SuiteGateway.php';
$db=new PDO('mysql:host=127.0.0.1;port=3306;dbname=dabs_ci;charset=utf8mb4','root','dabs-ci-only',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec(file_get_contents(dirname(__DIR__).'/database/suite-empty-schema.sql'));
$temp=sys_get_temp_dir().'/dabs-install-'.bin2hex(random_bytes(8));
mkdir($temp,0700);mkdir($temp.'/web',0700);mkdir($temp.'/web/includes',0700);mkdir($temp.'/web/bin',0700);mkdir($temp.'/private',0700);mkdir($temp.'/sessions',0700);
$files=['includes/SuiteGateway.php','includes/db_connect.php','includes/database_config.php','bin/provision-suite.php','suite-gate.php'];
foreach($files as$file)copy(dirname(__DIR__).'/'.$file,$temp.'/web/'.$file);
$b=['instance_id'=>16,'organization_id'=>10,'project_id'=>10,'local_project_id'=>1,'module_key'=>'dabs','origin'=>'https://carefoot.dabs.defecttracker.uk','key'=>str_repeat('a',64),'session_path'=>$temp.'/sessions'];
$binding=$temp.'/private/dabs-suite.php';file_put_contents($binding,'<?php return '.var_export($b,true).';');chmod($binding,0600);
putenv('DABS_SUITE_CONFIG='.$binding);
foreach(['HOST'=>'127.0.0.1','PORT'=>'3306','NAME'=>'dabs_ci','USER'=>'root','PASSWORD'=>'dabs-ci-only']as$key=>$value)putenv('DABS_DB_'.$key.'='.$value);
$checks=0;
$run=static function(string $mode)use($temp,&$checks):array{
 $p=proc_open([PHP_BINARY,$temp.'/web/bin/provision-suite.php',$mode],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
 $out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($p);
 $reply=json_decode($out,true);
 if($exit!==0||($reply['ok']??false)!==true||$err!=='')throw new RuntimeException('Installer check failed: '.$mode);
 $checks++;return $reply;
};
try{
 $r=$run('--check');if(($r['schema_empty']??false)!==true)throw new RuntimeException('Preflight failed.');
 if((int)$db->query('SELECT COUNT(*) FROM projects')->fetchColumn()!==0)throw new RuntimeException('Preflight wrote records.');$checks++;
 $run('--install');DabsSuiteGateway::database($db,$b);$checks++;
 if((int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn()!==0)throw new RuntimeException('Installer created native credentials.');$checks++;
 $r=$run('--install');if(($r['already_provisioned']??false)!==true||($r['writes_performed']??-1)!==0)throw new RuntimeException('Rerun changed installation.');
 if((int)$db->query('SELECT COUNT(*) FROM projects')->fetchColumn()!==1)throw new RuntimeException('Duplicate project.');$checks++;
 echo json_encode(['ok'=>true,'checks'=>$checks,'ci_database_only'=>true,'live_isolation_verified'=>false]).PHP_EOL;
}finally{
 putenv('DABS_SUITE_CONFIG');foreach(['HOST','PORT','NAME','USER','PASSWORD']as$key)putenv('DABS_DB_'.$key);
 foreach(array_reverse($files)as$file)unlink($temp.'/web/'.$file);
 @unlink($temp.'/web/.user.ini');@unlink($temp.'/web/.suite-required');unlink($binding);
 foreach(['web/includes','web/bin','web','private','sessions','']as$dir)rmdir($temp.($dir!==''?'/'.$dir:''));
}
