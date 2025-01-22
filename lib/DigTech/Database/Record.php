<?php

namespace DigTech\Database;

use DigTech\Logging\Logger as Logger;

/**
 * \class Record
 * \ingroup Database
 * \brief This class provides the core functionality of a database record object
 */
class Record
{
   protected $_conn;          ///< Database connection object
   protected $_tableName;     ///< Name of target table
   protected $_primaryKeys;   ///< Array of primary key columns
   protected $_columnList;    ///< Array of columns and values
   protected $_dirtyFlags;    ///< Array of dirty column flags
   protected $_readOnlyFlags; ///< Array of read-only column flags
   protected $_noQuoteFlags;  ///< Array of no-quote flags
   /**
    * \brief Object constructor
    * \param $conn Database connection object reference
    * \param $tableName Name of target table
    * \param $primaryKeys Array of primary key columns
    */
   public function __construct(&$conn, $tableName, $primaryKeys)
   {
      $this->_conn        = $conn;
      $this->_tableName   = $tableName;
      $this->_primaryKeys = $primaryKeys;

      $this->reset();
   }

   /**
    * \brief Reset the column list and dirty flags
    */
   protected function reset()
   {
      $this->_columnList  = [];
      $this->_dirtyFlags  = [];
   }

   /**
    * \brief Get the value of a column
    * \param $columnName The name of the column to retrieve
    * \returns Value of column or null
    */
   public function get($columnName)
   {
      $ret = null;

      $ret = $this->_columnList[$columnName] ?? null;

      return $ret;
   }

   /**
    * \brief Set the value of a column
    * \param $columnName The name of the column to set
    * \param $value The value to store in the column
    */
   public function set($columnName, $value)
   {
      $this->_columnList[$columnName] = $this->_columnList[$columnName] ?? null;

      if($this->_columnList[$columnName] !== $value)
      {
         $this->_columnList[$columnName] = $value;
         $this->_dirtyFlags[$columnName] = true;
         if($this->isKeyColumn($columnName))
         {
            $this->setKey($columnName, $value);
         }
      }
   }

   /**
    * \brief Set the value of a key field
    * \param $columnName The name of the key to set
    * \param $value The value to store in the key
    */
   public function setKey($columnName, $value)
   {
      $this->_primaryKeys[$columnName] = $value;
   }

   /**
    * \brief Check of column is in primary key
    * \param $columnName The name of the column to check
    * \retval true The column is in the primary key
    * \retval false The column is not in the primary key
    * \returns Boolean true/false if column is in primary key
    */
   public function isKeyColumn($columnName)
   {
      $ret = false;

      if(array_key_exists($columnName, $this->_primaryKeys))
      {
         $ret = true;
      }

      return $ret;
   }

   /**
    * \brief Build string for SQL WHERE to retrieve by primary key or keys parameter
    * \param $keys Array of column/value pairs to use for query ($keys[columnName] = value)
    */
   public function buildKeyString($keys=null)
   {
      $ret = '';

      if($keys === null)
      {
         $keys = $this->_primaryKeys;
      }
      foreach($keys as $columnName => $value)
      {
         if(strlen($ret))
         {
            $ret .= " AND ";
         }
         $ret .= sprintf("%s = '%s'", $columnName, $value);
      }

      return $ret;
   }

   /**
    * \brief Check of column is dirty
    * \param $columnName The name of the column to check
    * \retval true The column is dirty
    * \retval false The column is not dirty
    * \returns Boolean true/false if column is dirty
    */
   public function getDirty($columnName)
   {
      return $this->_dirtyFlags[$columnName] ?? false;
   }

   /**
    * \brief Set column is dirty
    * \param $columnName The name of the column to mark as dirty
    */
   public function setDirty($columnName)
   {
      $this->_dirtyFlags[$columnName] = true;
   }

   /**
    * \brief Clear column dirty flag
    * \param $columnName The name of the column to clear the dirty flag for
    */
   public function clearDirty($columnName)
   {
      $this->_dirtyFlags[$columnName] = false;
   }

   /**
    * \brief Check if record is dirty
    * \retval true A column in the record has been modified
    * \retval false No columns in the record have been modified
    * \returns Boolean true/false if record has been modified
    */
   public function isDirty()
   {
      $ret = false;

      foreach($this->_dirtyFlags as $flag)
      {
         if($flag === true)
         {
            $ret = true;
            break;
         }
      }

      return $ret;
   }

   /**
    * \brief Reset dirty flags for the record
    */
   public function resetDirty()
   {
      foreach($this->_dirtyFlags as $columnName => $flag)
      {
         $this->clearDirty($columnName);
      }
   }

   /**
    * \brief Set the read-only flag for a column
    * \param $columnName The name of the column name to set as read-only
    */
   public function setReadOnly($columnName)
   {
      $this->_readOnlyFlags[$columnName] = true;
   }

   /**
    * \brief Clear the read-only flag for a column
    * \param $columnName The name of the column name to clear as not read-only
    */
   public function clearReadOnly($columnName)
   {
      $this->_readOnlyFlags[$columnName] = false;
   }

   /**
    * \brief Get the read-only flag for a column
    * \retval true The column is read-only
    * \retval false The column is not read-only
    * \returns The read-only state for the column
    */
   public function isReadOnly($columnName)
   {
      return $this->_readOnlyFlags[$columnName] ?? null;
   }

   /**
    * \brief Set the no quote flag for a column
    * \param $columnName The name of the column name to set as no quote
    */
   public function setNoQuote($columnName)
   {
      $this->_noQuoteFlags[$columnName] = true;
   }

   /**
    * \brief Clear the no quote flag for a column
    * \param $columnName The name of the column name to clear as no quote
    */
   public function clearNoQuote($columnName)
   {
      $this->_noQuoteFlags[$columnName] = false;
   }

   /**
    * \brief Get the no quote flag for a column
    * \retval true The column is no quote
    * \retval false The column is not no quote
    * \returns The no quote state for the column
    */
   public function isNoQuote($columnName)
   {
      return $this->_noQuoteFlags[$columnName] ?? null;
   }

    /**
     * \brief Is this a system/administrative column?
     * \param $col Column name to check
     * \returns Whether column is a system/administrative column
     * \retval true Column is a system/adminstrative column
     * \retval false Column is not a system/adminstrative column
    */
    function isSystemColumn($col)
    {
       $ret = false;
 
       switch($col)
       {
          case 'create_by':
          case 'create_date':
          case 'modify_by':
          case 'modify_date':
             $ret = true;
             break;
       }
       
       return $ret;
    }
 
    /**
     * \brief Copy this object to another onea
     * \returns New DBRecord object that is a copy of this object
     */
    function copyRecord()
    {
       $rec = new Record($this->_conn, $this->_tableName, $this->_primaryKeys);
 
       foreach($this->_columnList as $col => $val)
       {
          $rec->set($col, $val);
       }
 
       return $rec;
    }
 
    /**
     * \brief Return an array containing column data
     * \returns Array containing column data
     */
    function getColumns()
    {
       return $this->_columnList;
    }
    /**
     * \brief Return a JSON strong containing column data
     * \returns JSON string containing column data
     * @return bool|string
     */
    function getJSON()
    {
       $json = json_encode($this->_columnList);
       return $json;
    }
   /**
    * \brief Read the record from the database
    */
   function read()
   {
      $ret = false;
      // make sure the table name and keys have been set
      if(isset($this->_tableName) &&
         count($this->_primaryKeys) > 0)
      {
         $keystr = $this->buildKeyString($this->_primaryKeys);
         // build the sql statement
         $sql = "SELECT *
                   FROM $this->_tableName
                  WHERE $keystr";
         $res = $this->_conn->query($sql);
         if($res)
         {
            // make sure we actually got a row
            if($this->_conn->numRows($res) > 0)
            {
               // clear the dirty column flags
               $this->resetDirty();
               // get the result row
               $this->_columnList = $this->_conn->fetch($res);
               // update the key values
               foreach($this->_columnList as $col => $val)
               {
                  if($this->isKeyColumn($col))
                  {
                     $this->setKey($col, $val);
                  }
               }
               // set the return flag to true
               $ret = true;
            }
            else
            {
/*
               Logger::log(LOG_TYPE_DATABASE,
                           LOG_LEVEL_ERROR,
                           "Record not found - %s - %s - %s",
                           $this->_dbconn->error(),
                           $this->_table,
                           $sql);
*/
            }
            $this->_conn->freeResult($res);
         }
         else
         {
            Logger::log("Error retrieving record - %s - %s - %s",
                        $this->_conn->error(),
                        $this->_tableName,
                        $sql);
         }
      }
      else
      {
         Logger::log("Table name not specified in read()");
      }

      return $ret;
   }

   /**
    * \brief Insert a new record into the database
    */
    function insert()
    {
       $ret = false;
       $cols = '';
       $vals = '';
       $index = 0;
 
       if(isset($this->_tableName))
       {
          foreach($this->_columnList as $col => $val)
          {
             // skip system/adminstrative columns
             if($this->isSystemColumn($col))
             {
                continue;
             }
 
             // skip primary key columns on INSERT
             if($this->isKeyColumn($col))
             {
                continue;
             }
 
             if(!$this->isReadOnly($col) && $this->getDirty($col))
             {
                // append separators after the first column
                if($index != 0)
                {
                   $cols .= ',';
                   $vals .= ',';
                }
                $cols .= $col;
 
                if($val === null)
                {
                   $vals .= 'NULL';
                }
                else if($this->isNoQuote($col))
                {
                   $vals .= $this->_conn->escapeString($val);
                }
                else
                {
                   $vals .= "'".$this->_conn->escapeString($val)."'";
                }
                $index++;
             }
          }
 
          // there were no columns to update
          if($index == 0)
          {
             return $ret;
          }
 
          // build the statement to execute
          $sql = "INSERT INTO $this->_tableName ($cols) VALUES ($vals)";
 
          // now let's execute it
          $res = $this->_conn->query($sql);
          if($res)
          {
             // HACK ALERT! there should be a better way to determine which key component should be pulled here
             // HACK ALERT! i'm using the first one in the list here, but caveat emptor
 
             // reset the array just in case
             reset($this->_primaryKeys);
             // get the first key value
             $key = key($this->_primaryKeys);
             // save the insert id in the proper column
             $this->set($key, $this->_conn->insertId());
             // reread the record to get auto-generated values
             $this->read();
             // set return value;
             $ret = true;
          }
          else
          {
             Logger::log("Error inserting record - %s - %s - %s",
                         $this->_conn->error(),
                         $this->_tableName,
                         $sql);
          }
       }
       else
       {
          Logger::log("Table not specified in insert()");
       }
 
       return $ret;
    }
 
    /**
     * \brief Update a record in the database
     */
    function update()
    {
       $ret = false;
       $cols = '';
       $index = 0;
 
       if(isset($this->_tableName) && is_array($this->_primaryKeys) && count($this->_primaryKeys) > 0)
       {
          foreach($this->_columnList as $col => $val)
          {
             // skip system/adminstrative columns
             if($this->isSystemColumn($col))
             {
                continue;
             }
 
             // skip primary key columns
             if($this->isKeyColumn($col))
             {
                continue;
             }
 
             // check if this column should be updated
             if(!$this->isReadOnly($col) && $this->getDirty($col))
             {
                if($index != 0)
                {
                   $cols .= ',';
                }
                if($val === null)
                {
                   $cols .= "$col = NULL";
                }
                else if($this->isNoQuote($col))
                {
                   $cols .= sprintf("%s = %s", $col, $this->_conn->escapeString($val));
                }
                else
                {
                   $cols .= sprintf("%s = '%s'", $col, $this->_conn->escapeString($val));
                }
                $index++;
             }
          }
 
          // there were no columns to update
          if($index == 0)
          {
             $ret = true;
             return $ret;
          }
 
          // build the sql statement to update the record
          $keystr = $this->buildKeyString($this->_primaryKeys);
          $sql = "UPDATE $this->_tableName SET $cols WHERE $keystr";
          // execute the statement
          $res = $this->_conn->query($sql);
          if($res)
          {
             // reset the dirty column flags
             $this->resetDirty();
             // set the return code
             $ret = true;
          }
          else
          {
             Logger::log("Error updating record - %s - %s - %s",
                         $this->_conn->error(),
                         $this->_tableName,
                         $sql);
          }
       }
 
       return $ret;
    }
 
    /**
     * \brief Delete a record from the database
     */
    function delete()
    {
       $ret = false;
 
       if(isset($this->_tableName) && is_array($this->_primaryKeys) && count($this->_primaryKeys) > 0)
       {
          // build the sql statement to delete the record
          $keystr = $this->buildKeyString($this->_primaryKeys);
          $sql = "DELETE FROM $this->_tableName WHERE $keystr";
          // execute the sql statement
          $res = $this->_conn->query($sql);
          if($res)
          {
             $ret = true;
          }
          else
          {
             Logger::log("Error deleting record - %s - %s - %s",
                         $this->_conn->error(),
                         $this->_tableName,
                         $sql);
          }
       }
       else
       {
          Logger::log("Table not specified in delete()");
       }
 
       return $ret;
    }
  }
