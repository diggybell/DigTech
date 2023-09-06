<?php

namespace DigTech\Database;

/**
 * \class Connection
 * \ingroup Database
 * \brief Base class for all database connections. Abstract functions connection to database specific connectors.
 */
abstract class Connection
{
   protected $_host;       ///< Hostname of database
   protected $_port;       ///< Port number
   protected $_username;   ///< User name
   protected $_password;   ///< Password
   protected $_schema;     ///< Default schema name
   protected $_conn;       ///< Database connection handle

   /**
    * \brief Object constructor
    */
   public function __construct()
   {
      $this->_host = 'localhost';
      $this->_port = '3306';
      $this->_username = '';
      $this->_password = '';
      $this->_schema = '';
      $this->_conn = null;
   }

   /**
    * \brief Object destructor
    */
   function __destruct()
   {
      $this->disconnect();
   }

   /**
    * \brief Set host name
    * \param $host Host name to connect to
    */
   public function host($host)
   {
      $this->_host = $host;
   }

   /**
    * \brief Set port number
    * \param $port TCP port number
    */
   public function port($port)
   {
      $this->_port = $port;
   }

   /**
    * \brief Set user name
    * \param $username User name for connection
    */
   public function username($username)
   {
      $this->_username = $username;
   }

   /**
    * \brief Set password
    * \param $password Password for connection
    */
   public function password($password)
   {
      $this->_password = $password;
   }

   /**
    * \brief Set default schema
    * \param $schema Default schema name
    */
   public function schema($schema)
   {
      $this->_schema = $schema;
   }

   /**
    * \brief Configure connection from array
    * Array = [
    *    'host' => 'localhost',
    *    'port' => 3306,
    *    'username' => '',
    *    'password' => '',
    *    'schema' => '',
    * ]
    */
   public function configure($configuration)
   {
      $this->_host = $configuration->host ?? 'localhost';
      $this->_port = $configuration->port ?? 3306;
      $this->_username = $configuration->username;
      $this->_password = $configuration->password;
      $this->_schema = $configuration->schema;
   }

   /**
    * \brief Connect to database
    */
   abstract public function connect();

   /**
    * \brief Disconnect from database
    */
   abstract public function disconnect();

   /**
    * \brief Query database
    * \param $sql Query statement to execute
    * \param $multi Indicates statement string contains multiple statements
    */
   abstract public function query($sql, $multi=false);

   /**
    * \brief Fetch a row from the database
    * \param $res Result set to retrieve row for
    */
   abstract public function fetch($res);

   /**
    * \brief Free result set
    * \param $res Result set to free
    */
   abstract public function freeResult($res);

   /**
    * \brief Get the last error information
    */
   abstract public function error();

   /**
    * \brief Number of rows in result set
    * \param $res Result set to get number of rows for
    */
   abstract public function numRows($res);

   /**
    * \brief Number of rows affected by operation
    * \param $res Result set to get affected rows for
    */
   abstract public function affectedRows($res);

   /**
    * \brief Get the last inserted identity value
    */
   abstract public function insertId();

   /**
    * \brief Escape a string for SQL injection protection
    */
    abstract public function escapeString($string);

}

?>
