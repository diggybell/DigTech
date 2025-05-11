<?php

include_once('../lib/autoload.php');
include_once('../lib/autoconfig.php');

use \DigTech\Core\Configuration as Configuration;
use \DigTech\Logging\Logger as Logger;
use \DigTech\REST\Client as Client;

$cfg = getGlobalConfiguration();

$client = new Client('http://digtech.diggyabi.com/api/v1/healthcheck?check=true');

$client->setUserName('user');
$client->setPassword('password');
$client->setToken('No-Op');
$client->setAppToken('No-Op');

$result = $client->callService('GET');
var_dump($result);
