#!/usr/bin/php -q
<?php


include_once('../autoload.php');
include_once('../autoconfig.php');

use DigTech\Database\MySQL\Connection as Connection;
use DigTech\Logging\Logger as Logger;

$dbName = (isset($argv[1])) ? $argv[1] : 'digtech_testdata';

$db = new Connection();
$db->username('diggy');
$db->password('Fender78');
$db->schema($dbName);

if($db->connect())
{
   $sql = "SHOW TABLES";
   $res = $db->query($sql);
   if($res)
   {
      while($row = $db->fetch($res))
      {
         $hasAudit = false;
         $table = reset($row);
         $sql = "DESCRIBE $table";
         $res2 = $db->query($sql);
         if($res2)
         {
            while($row2 = $db->fetch($res2))
            {
               switch($row2['Field'])
               {
                  case 'create_date':
                  case 'create_by':
                  case 'modify_date':
                  case 'modify_by':
                     $hasAudit = true;
                     break;
               }
            }
            $db->freeResult($res2);
         }
         if($hasAudit)
         {
?>

USE <?php echo $dbName; ?>;

DROP TRIGGER IF EXISTS <?php echo $table; ?>_insert;
DROP TRIGGER IF EXISTS <?php echo $table; ?>_update;

DELIMITER $$
CREATE TRIGGER <?php echo $table; ?>_insert BEFORE INSERT ON <?php echo $table; ?> 
   FOR EACH ROW
   BEGIN
      SET NEW.create_date = NOW();
      SET NEW.create_by   = CURRENT_USER;
      SET NEW.modify_date = '0000-00-00 00:00:00';
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER <?php echo $table; ?>_update BEFORE UPDATE ON <?php echo $table; ?> 
   FOR EACH ROW
   BEGIN
      SET NEW.modify_date = NOW();
      SET NEW.modify_by   = CURRENT_USER;
   END; $$
DELIMITER ;
<?php
         }
      }
      $db->freeResult($res);
   }
}
else
{
   printf("Unable to connect to database\n");
}

?>
