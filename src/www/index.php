<html>
<head>
<title>DigTech Demonstrator</title>
</head>
<body>
<h1>DigTech Demonstrator</h1>

<?php

$path = '/home/diggy/source/php-lib';

include_once($path . '/lib/autoload.php');
include_once($path . '/lib/autoconfig.php');

use \DigTech\Logging\Logger as Logger;
use \DigTech\Database\MySQL as MyDB;
use \DigTech\Database\Record as Record;

$cfg = getGlobalConfiguration();

$c = new MyDB\Connection();

$config = $cfg->getSection('db-digtech');
$c->configure($config);
Logger::setEnabled(true);

if($c->connect())
{
   printf("<p>Execution Timestamp: %s", date('Y-m-d H:i:s'));
}

?>

</body>
</html>
