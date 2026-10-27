<?php
declare(strict_types=1);
// Disposable local fixture databases; never accepts a live database connection.
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
require dirname(__DIR__).'/includes/SuiteGateway.php';
$directory=sys_get_temp_dir().'/dabs-ui-'.bin2hex(random_bytes(8));mkdir($directory,0700);
$schema=(string)file_get_contents(dirname(__DIR__).'/database/suite-empty-schema.sql');
$schema=preg_replace('/--[^\n]*\n/','',$schema);
$checks=0;
foreach(['alpha'=>[171,71,'TEST Suite Demo Build Co','TEST Riverside Training Project','TEST Northside Electrical'],
         'beta'=>[172,72,'TEST Other Building Co','TEST Hilltop Training Project','TEST Westfield Joinery']]as$site=>[$instance,$org,$company,$project,$contractor]) {
    $db=new PDO('sqlite:'.$directory.'/'.$site.'.sqlite',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    foreach(explode(';',$schema)as$sql) {
        $sql=trim($sql);if(!str_starts_with($sql,'CREATE TABLE'))continue;
        $sql=preg_replace('/\s+COMMENT\s+\x27(?:\x27\x27|[^\x27])*\x27/','',$sql);
        $sql=preg_replace('/\s+ENGINE=.*$/s','',$sql);
        $sql=preg_replace('/enum\([^)]*\)/i','TEXT',$sql);
        $sql=preg_replace('/`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY/','`id` INTEGER PRIMARY KEY AUTOINCREMENT',$sql);
        $sql=str_replace(' ON UPDATE CURRENT_TIMESTAMP','',$sql);
        $sql=preg_replace('/UNIQUE KEY `[^`]+`/','UNIQUE',$sql);
        $db->exec($sql);
    }
    $db->exec('CREATE TABLE suite_deployment(id INTEGER PRIMARY KEY,instance_id INT,organization_id INT,project_id INT,local_project_id INT,module_key TEXT)');
    $q=$db->prepare("INSERT INTO suite_deployment VALUES(1,?,?,?,1,'dabs')");$q->execute([$instance,$org,$org]);
    $q=$db->prepare("INSERT INTO projects(id,name,location,manager,start_date,status) VALUES(1,?,'TEST location','TEST Morgan',?,'active')");$q->execute([$project,date('Y-m-d')]);
    $q=$db->prepare("INSERT INTO branding_settings(id,company_name,tagline,company_logo) VALUES(1,?,?,'images/suite-dabs.svg')");$q->execute([$company,$project]);
    $q=$db->prepare("INSERT INTO users(id,username,password,name,email,role) VALUES(1,'test-morgan',?,'TEST Morgan','morgan@example.invalid','admin')");$q->execute([password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT)]);
    $q=$db->prepare("INSERT INTO briefings(id,project_id,date,overview,safety_info,notes,created_by,last_updated,status) VALUES(1,1,?,'TEST morning coordination','<p>TEST Keep access routes clear.</p>','TEST coordination notes',1,?,'draft')");$q->execute([date('Y-m-d'),date('Y-m-d H:i:s')]);
    $q=$db->prepare("INSERT INTO dabs_subcontractors(project_id,name,trade,contact_name,phone,email,status) VALUES(1,?,'TEST Trade','TEST Casey','00000000000','casey@example.invalid','Active')");$q->execute([$contractor]);
    $q=$db->prepare("INSERT INTO activities(briefing_id,date,time,title,description,area,priority,labor_count,contractors,completed) VALUES(1,?,'08:30','TEST interface inspection','TEST review access and work areas','Ground floor','medium',4,?,0)");$q->execute([date('Y-m-d'),$contractor]);
    $db->exec("INSERT INTO resources(briefing_id,name,type,location,assigned_to) VALUES(1,'TEST work team','personnel','Ground floor','TEST Casey')");
    $q=$db->prepare("INSERT INTO dabs_notes(project_id,note_date,notes,updated_by) VALUES(1,?,'<p>TEST morning coordination notes.</p>','TEST Morgan')");$q->execute([date('Y-m-d')]);
    $q=$db->prepare("INSERT INTO dabs_attendees(project_id,briefing_date,attendee_name,subcontractor_name,email,added_by) VALUES(1,?,'TEST Casey',?,'casey@example.invalid','TEST Morgan')");$q->execute([date('Y-m-d'),$contractor]);
    $binding=['instance_id'=>$instance,'organization_id'=>$org,'project_id'=>$org,'local_project_id'=>1,'module_key'=>'dabs','origin'=>'https://'.$site.'.dabs.defecttracker.uk','key'=>str_repeat('a',64)];
    DabsSuiteGateway::database($db,$binding);$checks++;
    try {DabsSuiteGateway::database($db,array_replace($binding,['organization_id'=>$org+99]));throw new LogicException('Wrong company accepted.');}catch(RuntimeException $expected){$checks++;}
}
echo json_encode(['directory'=>$directory,'databases'=>2,'binding_checks'=>$checks,'live_database_access'=>false]).PHP_EOL;
