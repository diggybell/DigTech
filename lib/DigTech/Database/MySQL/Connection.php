<?php

namespace DigTech\Database\MySQL;

use DigTech\Database\Connection as BaseConnection;
use DigTech\Logging\Logger as Logger;

/**
 * \class Connection
 * \ingroup Database
 * \brief Provides the MySQL database connection and API
 */
class Connection extends BaseConnection
{
   public function __construct()
   {
      parent::__construct();

      $this->_host = 'localhost';
      $this->_port = 3306;
   }
   public function connect()
   {
      $ret = false;

      if($this->_conn != null)
      {
         $this->disconnect();
      }

      Logger::log("Connecting to database (%s/%s/%s/%s)\n",
                  $this->_host,
                  $this->_username,
                  '********',
                  $this->_schema);
      
      $this->_conn = mysqli_init();
      if($this->_conn)
      {
         $ret = mysqli_real_connect($this->_conn,
                                    $this->_host,
                                    $this->_username,
                                    $this->_password,
                                    $this->_schema,
                                    $this->_port);
         if($ret == true)
         {
         }
         else
         {
            Logger::error("Error connecting to database at %s for %s (%s)\n",
            $this->_host,
            $this->_username,
            $this->error());
         }
      }
      else
      {
         Logger::error("Error creating database object (%s)\n",
                       $this->error());
      }

      return $ret;
   }

   public function disconnect()
   {
      if($this->_conn != null)
      {
         mysqli_close($this->_conn);
         $this->_conn = null;
         Logger::log("Disconnecting from database (%s/%s/%s/%s)\n",
                     $this->_host,
                     $this->_username,
                     '********',
                     $this->_schema);
      }
   }
 
   public function query($sql, $multi=false)
   {
      //Logger::log(LOG_TYPE_DATABASE, LOG_LEVEL_DEBUG, "SQL: %s", $sql);

      if($multi)
      {
         $res = mysqli_multi_query($this->_conn, $sql);
         do
         {
            if($res = mysqli_store_result($this->_conn))
            {
               if(mysqli_num_rows($res) > 0)
               {
                  break;
               }
            }
         } while(mysqli_next_result($this->_conn));
      }
      else
      {
         $res = mysqli_query($this->_conn, $sql);
      }
      if($res === false)
      {
         Logger::log("Query failed - %s - %s\n",
                     $this->error(),
                     $sql);
      }

      return $res;
   }
 
   public function fetch($res)
   {
      return mysqli_fetch_assoc($res);
   }
 
   public function freeResult($res)
   {
      mysqli_free_result($res);
   }
 
   public function error()
   {
      return mysqli_error($this->_conn);
   }
 
   public function numRows($res)
   {
      return mysqli_num_rows($res);
   }
 
   public function affectedRows($res)
   {
      return mysqli_affected_rows($this->_conn);
   }
 
   public function insertId()
   {
      return mysqli_insert_id($this->_conn);
   }

   public function escapeString($string)
   {
      $ret = $string;
      if(is_string($string))
      {
         $ret = mysqli_real_escape_string($this->_conn, $string);
      }
      return $ret;

   }
}

?>
