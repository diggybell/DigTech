<?php

/**
 * \file test_table.php
 * \brief This file was generated on 2023-08-14 09:24:16 by generate-crud-api.php
 */

use DigTech\REST\CRUDResource as CRUDResource;
use DigTech\Logging\Logger as Logger;

/**
 * \class test_table
 */

class test_table extends CRUDResource
{
   protected $_tableName = 'test_table';
   protected $_primaryKey = 'rec_seq';
   protected $_userToDB =
   [
      'RecSeq' => 'rec_seq',
      'RecName' => 'rec_name',
      'RecTstamp' => 'rec_tstamp',
      'CreateBy' => 'create_by',
      'CreateDate' => 'create_date',
      'ModifyBy' => 'modify_by',
      'ModifyDate' => 'modify_date',
   ];
   protected $_dbToUser =
   [
      'rec_seq' => 'RecSeq',
      'rec_name' => 'RecName',
      'rec_tstamp' => 'RecTstamp',
      'create_by' => 'CreateBy',
      'create_date' => 'CreateDate',
      'modify_by' => 'ModifyBy',
      'modify_date' => 'ModifyDate',
   ];

   public function __construct($request, $schema='digtech')
   {
      parent::__construct($request, $schema);

      $this->addVariable('RecSeq', 'n');
      $this->addVariable('RecName', 's');
      $this->addVariable('RecTstamp', 'dt');
      $this->addVariable('CreateBy', 's');
      $this->addVariable('CreateDate', 'dt');
      $this->addVariable('ModifyBy', 's');
      $this->addVariable('ModifyDate', 'dt');
   }
};

?>
