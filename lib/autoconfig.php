<?php

use DigTech\Core\Configuration as Configuration;

global $globalConfiguration;  ///< The global configuration object

getGlobalConfiguration();

/**
 * \brief Create the configuration object and load ini file. Save to global variable.
 * \returns Global configuration object
 */
function getGlobalConfiguration()
{
   global $globalConfiguration;

   if(!isset($globalConfiguration))
   {
      $globalConfiguration = new Configuration();
   }
   return $globalConfiguration;
}
