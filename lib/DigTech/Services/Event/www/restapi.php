<?php

$path = '/home/diggy/source/digtech/DigTech/lib';

include_once($path . '/autoload.php');
include_once($path . '/autoconfig.php');

use \DigTech\Logging\Logger as Logger;
use \DigTech\Database\MySQL as MyDB;
use \DigTech\Database\Record as Record;
use \DigTech\REST\APIInterface as APIInterface;

$cfg = getGlobalConfiguration();

$c = new MyDB\Connection();

$config = $cfg->getSection('logging');
Logger::configure($config);

$config = $cfg->getSection('db-digtech');
$c->configure($config);

if($c->connect())
{
   APIInterface::serviceRequest('/api/v1/');
}
