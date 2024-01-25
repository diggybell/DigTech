<?php

namespace DigTech\Database\PostgreSQL;

use DigTech\Database\Connection as BaseConnection;

/**
 * \class Connection
 * \ingroup Database
 * \brief Provides the PostgreSQL database connection and API
 */
class Connection extends BaseConnection
{
   public function __construct()
   {
      parent::__construct();
   }
}

