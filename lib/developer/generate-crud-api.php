<?php

include_once('../autoload.php');
include_once('../autoconfig.php');

use DigTech\Database\MySQL\Connection as Connection;
use DigTech\Logging\Logger as Logger;

function createVarName($col_name)
{
   $var = '';
   $words = explode('_', $col_name);
   foreach($words as $index => $word)
   {
      $var .= ucfirst($word);
   }
   return $var;
}

function mapDataTypes($sqlType)
{
   $ret = null;

   switch($sqlType)
   {
      case 'char':
      case 'varchar':
      case 'text':
         $ret = 's';
         break;
      case 'tinyint':
      case 'smallint':
      case 'mediumint':
      case 'bigint':
      case 'int':
         $ret = 'n';
         break;
      case 'double':
      case 'float':
         $ret = 'f';
         break;
      case 'date':
         $ret = 'd';
         break;
      case 'time':
         $ret = 't';
         break;
      case 'datetime':
      case 'timestamp':
         $ret = 'dt';
         break;
   }
   return $ret;
}

/**
 * generate-rest.php {host} {user} {password} {database} {table} {keyfield}
 */

if($argc != 7)
{
   printf("USAGE: php generate-crud-api.php <host> <user> <password> <database> <table> <keycol>\n");
   printf("Copyright 2023 (c) - DigTech\n");
   printf("\n");
   printf("   host     - database host name\n");
   printf("   user     - database user name\n");
   printf("   password - database user password\n");
   printf("   database - database name\n");
   printf("   table    - target table name\n");
   printf("   keycol   - target primary key column\n");
   printf("\n");

   exit(1);
}

$host     = $argv[1] ?? 'localhost';
$user     = $argv[2] ?? 'digtech';
$password = $argv[3] ?? 'digtech';
$database = $argv[4] ?? 'digtest_testdata';
$table    = $argv[5] ?? 'test_table';
$keycol   = $argv[6] ?? 'rec_seq';

$pieces = explode('_', $keycol);
$keyfld = '';
foreach($pieces as $piece)
{
   $keyfld .= ucfirst($piece);
}

$fieldNames = [];
$columnNames = [];
$dataTypes = [];

// disable logging so output can be captured
Logger::setEnabled(false);

$db = new Connection();

$db->host($host);
$db->username($user);
$db->password($password);
$db->schema($database);

if($db->connect())
{
   $sql = "DESCRIBE $table";
   $res = $db->query($sql);
   if($res)
   {
      while($row = $db->fetch($res))
      {
         // create user level column name (my_col = MyCol)
         $var = createVarName($row['Field']);

         // create mapping for db -> user and user -> db column names
         $fieldNames[$var] = $row['Field'];
         $columnNames[$row['Field']] = $var;

         // breakout the data type information for the column
         list($type, $length,$dec) = sscanf($row['Type'], "%[^(](%d,%d)");

         $dataTypes[$var]['type']    = $type;
         $dataTypes[$var]['length']  = (isset($length)) ? $length : 0;
         $dataTypes[$var]['dec']     = (isset($dec)) ? $dec : 0;
         $dataTypes[$var]['prikey']  = ($row['Key'] == 'PRI') ? 1 : 0;
         $dataTypes[$var]['autoinc'] = ($row['Extra'] == 'auto_increment') ? 1 : 0;
      }
   }
}
else
{
   printf("Unable to connect to database - %s\n", $db->error());
}

printf("<?php\n");
?>

/**
 * \file <?php printf("%s.php\n", $table); ?>
 * \brief <?php printf("This file was generated on %s by %s\n", date('Y-m-d h:i:s'), $argv[0]); ?>
 */

use DigTech\REST\CRUDResource as CRUDResource;
use DigTech\Logging\Logger as Logger;

/**
 * \class <?php printf("%s\n", $table); ?>
 */

class <?php printf("%s", $table); ?> extends CRUDResource
{
   protected $_tableName = '<?php echo $table; ?>';
   protected $_primaryKey = '<?php echo $keycol; ?>';
   protected $_userToDB =
   [
<?php
   foreach($fieldNames as $field => $column)
   {
      printf("      '%s' => '%s',\n", $field, $column);
   }
?>
   ];
   protected $_dbToUser =
   [
<?php
   foreach($columnNames as $column => $field)
   {
      printf("      '%s' => '%s',\n", $column, $field);
   }
?>
   ];

   public function __construct($request, $schema='digtech')
   {
      parent::__construct($request, $schema);

<?php
      foreach($fieldNames as $field => $column)
      {
         printf("      \$this->addVariable('%s', '%s');\n", $field, mapDataTypes($dataTypes[$field]['type']));
      }
?>
   }
};

<?php
printf("?>\n");
?>
