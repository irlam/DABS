<?php
declare(strict_types=1);
// Test-server router only; never usable through a normal public PHP/FPM request.
if(PHP_SAPI!=='cli-server'||getenv('DABS_UI_FIXTURE')!=='1'||($_SERVER['REMOTE_ADDR']??'')!=='127.0.0.1') {http_response_code(404);exit;}
$file=getenv('DABS_FIXTURE_DB')?:'';
if(!str_starts_with($file,sys_get_temp_dir().'/dabs-ui-')||!is_file($file)) {http_response_code(503);exit;}
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if(preg_match('~^/(?:css|js|images|assets)/[a-zA-Z0-9_./-]+$~D',$path)&&!str_contains($path,'..'))return false;
if(!in_array($path,['/','/index.php','/activity_schedule_tabs.php','/admin_panel.php','/ajax_activities.php','/ajax_notes.php','/ajax_safety.php','/ajax_attendees.php','/ajax_subcontractors.php','/ajax_resource_stats.php'],true)) {http_response_code(404);exit;}
foreach(['HOST'=>'127.0.0.1','NAME'=>'unused_fixture','USER'=>'fixture','PASSWORD'=>'fixture-only-not-used']as$key=>$value)putenv('DABS_DB_'.$key.'='.$value);
require dirname(__DIR__).'/includes/db_connect.php';
// Fixture dialect adapter only; production and the clean-install CI use MySQL.
class DabsFixtureStatement extends PDOStatement {
    protected function __construct() {}
    public ?int $schemaRows=null;
    public function rowCount():int{return $this->schemaRows??parent::rowCount();}
}
class DabsFixtureDatabase extends PDO {
    public function query(string $query,?int $fetchMode=null,mixed ...$args):PDOStatement|false {
        if(preg_match("/^SHOW COLUMNS FROM ([a-z_]+) LIKE '([a-z_]+)'$/i",$query,$m)) {
            $sql="SELECT name FROM pragma_table_info('".$m[1]."') WHERE name='".$m[2]."'";
            $count=count(parent::query($sql)->fetchAll());$statement=parent::query($sql);$statement->schemaRows=$count;return $statement;
        }
        return $fetchMode===null?parent::query($query):parent::query($query,$fetchMode,...$args);
    }
}
$pdo=new DabsFixtureDatabase('sqlite:'.$file,null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_STATEMENT_CLASS=>[DabsFixtureStatement::class,[]]]);
$pdo->sqliteCreateFunction('NOW',static fn()=>date('Y-m-d H:i:s'),0);
$pdo->sqliteCreateFunction('FIELD',static function($value,...$items){$i=array_search($value,$items,true);return $i===false?0:$i+1;},-1);
foreach(['DATE_FORMAT','TIME_FORMAT']as$function)$pdo->sqliteCreateFunction($function,static fn($date,$format)=>date(strtr($format,['%d'=>'d','%m'=>'m','%Y'=>'Y','%H'=>'H','%i'=>'i','%s'=>'s']),strtotime($date)),2);
session_start();
$_SESSION['authenticated']=true;$_SESSION['user_id']=1;$_SESSION['current_project']=1;$_SESSION['user_name']='TEST Morgan';
$_SESSION['user_role']=getenv('DABS_FIXTURE_ROLE')==='viewer'?'viewer':'admin';
$_SESSION['csrf_token']??=bin2hex(random_bytes(32));
require dirname(__DIR__).($path==='/'?'/index.php':$path);
