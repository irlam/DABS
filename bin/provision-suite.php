<?php
declare(strict_types=1);
/** Dedicated target only. Loads schema already reviewed/imported WITHOUT seed data. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
ini_set('display_errors','0');ini_set('log_errors','0');$phase='arguments';
try{
 if($argc!==2||!in_array($argv[1],['--check','--install'],true))throw new RuntimeException();
 $mode=$argv[1];$root=dirname(__DIR__);
 require $root.'/includes/SuiteGateway.php';
 if(!is_file($root.'/suite-gate.php')||is_link($root.'/suite-gate.php'))throw new RuntimeException('Missing gate code.');
 $phase='private binding';$b=DabsSuiteGateway::configuration($root);
 if($b['organization_id']!==10||$b['project_id']!==10||$b['local_project_id']!==1||$b['origin']!=='https://carefoot.dabs.defecttracker.uk')throw new RuntimeException();
 $phase='private database';require $root.'/includes/db_connect.php';$db=connectToDatabase();
 $phase='empty schema';$tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
 if(in_array('suite_deployment',$tables,true)){
   DabsSuiteGateway::database($db,$b);
   if(!is_file($root.'/.suite-required')||!is_file($root.'/.user.ini')||trim((string)file_get_contents($root.'/.user.ini'))!=='auto_prepend_file="'.$root.'/suite-gate.php"')throw new RuntimeException('Mandatory gate incomplete.');
   echo json_encode(['ok'=>true,'already_provisioned'=>true,'writes_performed'=>0,'live_isolation_verified'=>false]).PHP_EOL;exit;
 }
 foreach(['users','projects','briefings','activities','resources','dabs_subcontractors','dabs_attendees','branding_settings','activity_log']as$table)if(!in_array($table,$tables,true))throw new RuntimeException();
 foreach($tables as$table){if(!preg_match('/^[a-zA-Z0-9_]+$/D',$table)||(int)$db->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn()!==0)throw new RuntimeException('Target contains records.');}
 foreach(['.user.ini','.suite-required']as$file)if(file_exists($root.'/'.$file)||is_link($root.'/'.$file))throw new RuntimeException();
 $phase='required columns';
 foreach(['projects'=>['id','name','location','manager','start_date','status'],'users'=>['id','username','password','name','email','role'],'activities'=>['date','area','labor_count','contractors'],'branding_settings'=>['company_name','tagline']]as$table=>$columns){
   $actual=array_column($db->query('SHOW COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC),'Field');foreach($columns as$column)if(!in_array($column,$actual,true))throw new RuntimeException();
 }
 if($mode==='--check'){echo json_encode(['ok'=>true,'schema_empty'=>true,'writes_performed'=>0,'live_isolation_verified'=>false]).PHP_EOL;exit;}
 $phase='database metadata';
 $db->exec('CREATE TABLE suite_deployment(id INT PRIMARY KEY,instance_id BIGINT NOT NULL,organization_id BIGINT NOT NULL,project_id BIGINT NOT NULL,local_project_id BIGINT NOT NULL,module_key VARCHAR(20) NOT NULL) ENGINE=InnoDB');
 $db->exec('CREATE TABLE suite_users(suite_user_id BIGINT PRIMARY KEY,local_user_id INT NOT NULL UNIQUE,enabled TINYINT NOT NULL DEFAULT 1) ENGINE=InnoDB');
 $db->beginTransaction();
 $q=$db->prepare("INSERT INTO projects(id,name,location,manager,start_date,status) VALUES(1,?,?,?,?,'active')");
 $q->execute(['Leigh Infirmary – Jean Heys Unit 2','Jean Heys Unit 2, Leigh Infirmary, The Avenue, Leigh, WN7 1LX','Carefoot PLC',date('Y-m-d')]);
 $q=$db->prepare("INSERT INTO suite_deployment VALUES(1,?,?,?,?, 'dabs')");$q->execute([$b['instance_id'],10,10,1]);
 $q=$db->prepare("INSERT INTO branding_settings(id,company_name,tagline,company_logo) VALUES(1,?,?,?)");$q->execute(['Carefoot PLC','Leigh Infirmary – Jean Heys Unit 2','images/suite-dabs.svg']);
 $db->commit();DabsSuiteGateway::database($db,$b);
 $phase='mandatory gate';
 foreach(['.suite-required'=>"DABS dedicated Suite installation\n",'.user.ini'=>'auto_prepend_file="'.$root.'/suite-gate.php"'."\n"]as$file=>$content){
   $mask=umask(0077);try{$h=fopen($root.'/'.$file,'x');}finally{umask($mask);}if(!$h)throw new RuntimeException();try{if(fwrite($h,$content)!==strlen($content))throw new RuntimeException();}finally{fclose($h);}
 }
 echo json_encode(['ok'=>true,'provisioned'=>true,'instance_id'=>$b['instance_id'],'suite_registration_performed'=>false,'live_isolation_verified'=>false]).PHP_EOL;
}catch(Throwable $error){if(isset($db)&&$db->inTransaction())$db->rollBack();echo json_encode(['ok'=>false,'failed_phase'=>$phase,'live_isolation_verified'=>false]).PHP_EOL;exit(1);}
