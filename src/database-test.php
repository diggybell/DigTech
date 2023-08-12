<?php

include_once('../lib/autoload.php');
include_once('../lib/autoconfig.php');

use \DigTech\Logging\Logger as Logger;
use \DigTech\Database\MySQL as MyDB;
use \DigTech\Database\Record as Record;

$cfg = getGlobalConfiguration();

$c = new MyDB\Connection();

$config = $cfg->getSection('db-digtech');
$c->configure($config);

if($c->connect())
{
    $rec = new Record($c, 'test_table', ['rec_seq' => 0]);

    $rec->set('rec_name', 'Mickey Mouse');
    $rec->set('rec_tstamp', 'NOW()');
    $rec->setNoQuote('rec_tstamp');

    $rec->insert();

    printf("Insert: %s\n", $rec->get('rec_tstamp'));

    sleep(2);
    
    $rec->set('rec_tstamp', 'NOW()');
    $rec->update();

    $rec->read();

    printf("Update: %s\n", $rec->get('rec_tstamp'));

    $rec->delete();
}
else
{
    Logger::log("Database Connection Failed\n");
}

?>
