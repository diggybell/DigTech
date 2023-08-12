<?php

include_once('../lib/autoload.php');
include_once('../lib/autoconfig.php');

use \DigTech\Core\Configuration as Configuration;
use \DigTech\Logging\Logger as Logger;
use \DigTech\Database\MySQL as MyDB;

$cfg = getGlobalConfiguration();

$c = new MyDB\Connection();
/*
$c->host('localhost');
$c->port(3306);
$c->username('diggy');
$c->password('Fender78');
$c->schema('gsrs');
*/
$config = $cfg->getSection('db-gsrs');
$c->configure($config);

print_r($config);

if($c->connect())
{
    $res = $c->query("SHOW DATABASES;");
    if($res)
    {
        while($row = $c->fetch($res))
        {
            printf("%s\n", $row['Database']);
        }
        $c->freeResult($res);
    }
}
else
{
    Logger::log("Database Connection Failed\n");
}
//$r = new MyDB\Record();

?>
