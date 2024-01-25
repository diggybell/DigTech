<?php

$path = '/home/diggy/source/php-lib';

include_once($path . '/lib/autoload.php');
include_once($path . '/lib/autoconfig.php');

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
