<?php

namespace DigTech\REST;

//include_once('../../autoload.php');
//include_once('../../autoconfig.php');

use DigTech\Logging\Logger as Logger;

/**
 * \class APIInterface
 * \ingroup API
 * \brief This class is the primary dispatcher used to dispatch REST requests
 */
class APIInterface
{
   /**
    * \brief autoload handler for loading resources
    * \param $className The class name to load
    */
   protected static function autoloadResource($className)
   {
      Logger::log("Engine: Autoload Class %s\n", $className);
      $classFile = 'resources/' . $className . '.php';
      if (file_exists($classFile))
      {
         include_once($classFile);
      }
      else
      {
         Logger::log("Engine: Unable to load %s from %s\n", $className, $classFile);
         throw new \Exception("Unknown resource type ($className)");
      }
   }

   public static function phpErrorHandler($errno, $errstr, $file, $line)
   {
      if ($errno == E_NOTICE || $errno == E_STRICT || $errno == E_DEPRECATED)
         return false;

      Logger::log("ErrNo: %d ErrStr: %s File: %s Line: %d", $errno, $errstr, $file, $line);

      return false;
   }

   /**
    * \brief Handle a service request on behalf of the caller
    * \param $apiPrefix The path/URL element used to indicate the API request
    */
   public static function serviceRequest($apiPrefix)
   {
      error_reporting(1);
      set_error_handler(['\DigTech\REST\APIInterface', 'phpErrorHandler']);

      // register the autoload handler
      spl_autoload_register(['\DigTech\REST\APIInterface', 'autoloadResource'], true);

      // Requests from the same server don't have a HTTP_ORIGIN header
      if (!array_key_exists('HTTP_ORIGIN', $_SERVER))
      {
         $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
      }

      try
      {
         // get the initial request
         $request = $_SERVER['REQUEST_URI'];
         // separate the request from any query parameters
         list($request, $query) = explode('?', $request);
         // remove the api key from the request
         $request = str_replace($apiPrefix, '', $request);

         // separate out the query parameters
         $queryVars = [];
         if(strlen($query))
         {
            foreach(explode('&', $query) as $item)
            {
               list($var, $val) = explode('=', $item);
               $queryVars[$var] = $val;
            }
         }

         // get the resource name from the request URI
         list($resource) = explode('/', $request);
         // check to see if class exists (will autoload if class is defined)
         if (class_exists($resource, true))
         {
            // instantiate the resource handler (will autoload class file)
            $handler = new $resource($request);

            // save the query parameters
            $handler->setQueryVariables($queryVars);

            // call the resource handler
            printf("%s\n", $handler->dispatch());
         }
         else
         {
            Logger::log("Class not found %s\n", $resource);
            Logger::log("Request: %s\n", $request);
         }
      }
      catch (\Exception $e)
      {
         echo json_encode(array('error' => $e->getMessage()));
         Logger::log("Exception: %s\n", $e->getMessage());
      }
   }
}
