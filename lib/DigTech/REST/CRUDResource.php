<?php

namespace DigTech\REST;

use \DigTech\REST\SecureResource as SecureResource;
use \DigTech\Database\Record as Record;
use \DigTech\Logging\Logger as Logger;

/**
 * \class CRUDResource
 * \ingroup API
 * \brief This class provides core CRUD operation support.
 */

class CRUDResource extends SecureResource
{
   protected $_userToDB = [];
   protected $_dbToUser = [];
   protected $_tableName;
   protected $_primaryKey;

   public function __construct($request, $schema='digtech')
   {
      parent::__construct($request);
   }

   protected function isAuthorized()
   {
       $ret = false;

       $headers = getallheaders();
       if(isset($headers['Authorization']))
       {
           // get the user/password
           $credential = $headers['Authorization'];
           $credential = str_replace('Basic ', '', $credential);
           $credential = base64_decode($credential);
           list($user, $password) = explode(':', $credential);

           if($user == 'user' &&
              $password == 'password')
           {
               Logger::log("Credentials accepted for %s\n", $user);
               $ret = true;
           }
       }

       return $ret;
   }

  public function GET()
   {
      $result = 'Failed';
      $data = array();

      if($this->_conn->connect())
      {
         $seq = $this->_args[0];
         if($seq != 0)
         {
            $rec = new Record($this->_conn, $this->_tableName, [ $this->_primaryKey => $seq ]);
            if($rec->read())
            {
               foreach($this->_dbToUser as $column => $field)
               {
                  $data[$field] = $rec->get($column);
               }
               $result = 'Success';
            }
         }
         else
         {
            $fieldStr = '';
            foreach($this->_dbToUser as $col => $fld)
            {
               if(strlen($fieldStr))
               {
                  $fieldStr .= ', ';
               }
               $fieldStr .= "$col AS $fld";
            }

            $limit = '';
            if(isset($this->_request['limit']))
            {
               $limit = " LIMIT ".$this->_request['limit'];
               if(isset($this->_request['offset']))
               {
                  $limit .= " OFFSET ".$this->_request['offset'];
               }
            }
            $sql = sprintf("SELECT $fieldStr FROM %s%s", $this->_tableName, $limit);
            $res = $this->_conn->query($sql);
            if($res)
            {
               while($row = $this->_conn->fetch($res))
               {
                  $data[] = $row;
                  $result = 'Success';
               }
            }
         }
      }
      return [ 'status' => $result, 'data' => $data ];
   }

   public function POST()
   {
      $result = 'Failed';
      $data = array();

      if($this->_conn->connect())
      {
         $rec = new Record($this->_conn, $this->_tableName, array($this->_primaryKey => 0));

         foreach($this->_userToDB as $field => $column)
         {
            $val = $this->getRequestVariable($field, null);
            if($val !== null)
            {
               $rec->set($column, $val);
            }
         }

         if($rec->insert())
         {
            $data = array($this->_primaryKey => $rec->get($this->_primaryKey));
            $result = 'Success';
         }
         else
         {
            $data = array('ErrorMsg' => $this->_conn->error());
         }
      }
      else
      {
         Logger::log("Unable to connect to database: %s",
                     $this->_conn->error());
      }

      return [ 'status' => $result, 'data' => $data ];
   }

   public function PUT()
   {
      $result = 'Failed';
      $data = array();

      if($this->_conn->connect())
      {
         $seq = $this->getRequestVariable($this->_dbToUser[$this->_primaryKey], 0);

         $rec = new Record($this->_conn, $this->_tableName, array($this->_primaryKey => $seq));
         if($rec->read())
         {
            foreach($this->_userToDB as $field => $column)
            {
               $val = $this->getRequestVariable($field, null);
               if($val !== null)
               {
                  $rec->set($column, $val);
               }
            }

           if($rec->update())
            {
               $result = 'Success';
            }
            else
            {
               $data = array('ErrorMsg' => $this->_conn->error());
            }
         }
      }
      else
      {
         Logger::log("Unable to connect to database: %s",
                     $this->_conn->error());
      }

      return [ 'status' => $result, 'data' => $data ];
   }

   public function DELETE()
   {
      $result = 'Failed';
      $data = array();

      if($this->_conn->connect())
      {
         $seq = $this->_args[0];
         if($seq > 0)
         {
            $rec = new Record($this->_conn, $this->_tableName, array($this->_primaryKey => $seq));
            if($rec->read())
            {
              if($rec->delete())
               {
                  $result = 'Success';
               }
               else
               {
                  $data = array('ErrorMsg' => $this->_conn->error());
               }
            }
            else
            {
               $data = array('ErrorMsg' => $this->_conn->error());
            }
         }
         else
         {
            $data = array('ErrorMsg' => "Invalid sequence number ($seq)");
         }
      }
      else
      {
         Logger::log("Unable to connect to database: %s",
                     $this->_conn->error());
      }

      return [ 'status' => $result, 'data' => $data ];
   }
}
