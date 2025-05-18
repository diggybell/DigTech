<?php

namespace DigTech\Core;

/**
 * \class Configuration
 * \ingroup Core
 * \brief This class handles reading the application configuration file.
 * Features include dynamically locating the configuration file
 */
class Configuration
{
   protected $_config;                       ///< Parsed content of ini file
   protected $_configFile = 'digtech.ini';   ///< Default name of ini file
   protected $_defaultPaths = [];            ///< Default paths to search for ini file
   protected $_environment = '';             ///< The environment level application is running in (prod/qa/dev)
   protected $_runstate = '';                ///< The application is active or inactive
   
   /**
    * \brief Object constructor
    * \param $configFile Name of configuration file to open
    */
   public function __construct($configFile=null)
   {
      $this->_defaultPaths =
      [
         './',
         '/etc/',
         '/etc/digtech/',
         getenv('DIGTECH_CONFIG'),
      ];

      if($configFile !== null)
      {
         $this->_configFile = $configFile;
      }
      $this->_config = parse_ini_file($this->findConfigFile(), true);

      global $globalConfiguration;
      if($globalConfiguration === null)
      {
         $globalConfiguration = $this;
      }

      $this->_environment = $this->_config['global']['environment'];
      $this->_runstate = $this->_config['global']['runstate'];
   }

   /**
    * \brief Retrieve all configuration parameters from section as an object
    * \param $section The name of the ini section to retrieve
    * \returns Object containing configuration parameters
    */
   public function getSection($section)
   {
      $ret = [];
      
      if(isset($this->_config[$section]))
      {
         $ret = $this->_config[$section];
      }

      return (object)$ret;
   }

   /**
    * \brief Internal method to location ini file
    * \returns Location of ini file if found
    * \retval string Location of ini file
    * \retval null The ini file was not found
    */
   protected function findConfigFile()
   {
      $ret = null;

      if(file_exists($this->_configFile))
      {
         $ret = $this->_configFile;
      }
      else
      {
         $pathInfo = pathinfo($this->_configFile);
         foreach($this->_defaultPaths as $path)
         {
            if(substr($path, -1) !== DIRECTORY_SEPARATOR)
            {
               $path .= DIRECTORY_SEPARATOR;
            }

            if(file_exists($path . $pathInfo['basename']))
            {
               $ret = $path . $pathInfo['basename'];
               break;
            }
         }
      }

      return $ret;
   }

   /**
    * \brief Retrieve environment
    * \retval prod This is the production environment
    * \retval qa This is the qa environment
    * \retval dev This is thd development environment
    * \returns The environment application is running in
    */
   public function getEnvironment()
   {
      return $this->_config['global']['environment'];
   }

   /**
    * \brief Retrieve application run state
    * \retval active The application is running and should process requests
    * \retval inactivce The application is not running and should not process requests
    * \returns The run state for the application
    */
   public function getRunState()
   {
      return $this->_config['global']['runstate'];
   }
}
