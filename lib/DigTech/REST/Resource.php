<?php

namespace DigTech\REST;

use DigTech\Database\MySQL\Connection as Connection;
use DigTech\Logging\Logger as Logger;

/**
 * \class Resource
 * \ingroup API
 * \brief This abstract class serves as the base class for all REST APIs. This
 *        class cannot be instantiated directly.
 */
abstract class Resource
{
   protected $_method = '';      ///< HTTP request method
   protected $_resource = '';    ///< the requested resource name
   protected $_args = [];        ///< URI components following resource name
   protected $_query = [];       ///< query parameters from URI
   protected $_file = null;      ///< input file information for HTTP PUT
   protected $_request = [];     ///< sanitized input data ($_GET and $_PUT values)
   protected $_types = [];       ///< data types for input parameters
   protected $_conn = null;      ///< database connection handle for sql string sanitization
   protected $_schema = '';      ///< default database schema

   /**
    * \brief Construct the object, allow for CORS, assemble and pre-process the data
    * \param $request The request that was received
    * \param $schema The default schema to be used for this API
    */
   public function __construct($request, $schema=null)
   {
      $this->_schema = $schema;

      // allow CORS
      header("Access-Control-Allow-Orgin: *");
      header("Access-Control-Allow-Methods: *");

      // output will be encoded as json
      header("Content-Type: application/json");

      // extract URI arguments
      $this->_args = explode('/', rtrim($request));
      // extract resource name and remove from _args
      $this->_resource = array_shift($this->_args);

      // extract HTTP request method
      $this->_method = $_SERVER['REQUEST_METHOD'];
      // PUT or DELETE methods may be embedded in headers
      if ($this->_method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER))
      {
         if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE')
         {
            $this->_method = 'DELETE';
         }
         else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT')
         {
            $this->_method = 'PUT';
         }
         else
         {
            throw new \Exception("Unexpected Header");
         }
      }

      global $globalConfiguration;
      $globalConfiguration = getGlobalConfiguration();

      if($this->_schema !== null)
      {
         $conn = $globalConfiguration->getSection('db-'.$this->_schema);

         $this->_conn = new Connection();
         $this->_conn->configure($conn);
//         $this->_conn->connect();
      }

      $this->addVariable('request', 's');

      $this->_file = file_get_contents("php://input");

      // extract incoming data for the HTTP request method
      switch($this->_method)
      {
         case 'DELETE':
            $this->_request = $this->sanitizeData($_GET);
            break;
         case 'POST':
            $this->_request = $this->sanitizeData($_POST);
            break;
         case 'GET':
            $this->_request = $this->sanitizeData($_GET);
            break;
         case 'PUT':
            parse_str($this->_file, $vars);
            $parms = $this->sanitizeData($_GET);
            $vars = $this->sanitizeData($vars);
            $this->_request = array_merge($parms, $vars);
            break;
         default:
            $this->response('Invalid Method', 405);
            break;
      }

      // add default variables for limit/offset in queries
      $this->addVariable('limit', 'n');
      $this->addVariable('offset', 'n');

      $hdrs = getallheaders();
      foreach($hdrs as $hdr => $val)
      {
         Logger::log("Header: %s => %s\n", $hdr, $val);
      }

      $msg = "Method: $this->_method Resource: $this->_resource Args: {";
      foreach($this->_args as $index => $arg)
      {
         $msg .= "[$index]=>$arg ";
      }
      $msg .= "}";

      if(1)
      {
         $msg .= ' {';
         foreach($this->_request as $var => $arg)
         {
            $msg .= "[$var]=>$arg ";
         }
         $msg .= '}';
      }
      Logger::log("%s\n", $msg);
   }

   /**
    * \brief Add an input variable with type information (s - string, n - integer, f - float, d = date, t = time, dt = datetime, b = boolean)
    * \param $varname Variable name
    * \param $vartype Variable type
    */
   protected function addVariable($varname, $vartype)
   {
      $this->_types[$varname] = $vartype;
   }

   /**
    * \brief Process the request and return the response
    * \returns Output of service method
    */
   public function dispatch()
   {
      $output = null;

      // check to see if request method exists
      if ((int)method_exists($this, $this->_method) > 0)
      {
         // get the output of the requested method
         $output = $this->response($this->{$this->_method}($this->_args), 200);
      }
      else
      {
         // oops, method isn't supported 
         $output = $this->response("No Method: $this->_method", 405);
      }

      return $output;
   }

   /**
    * \brief Format the HTTP status header and json-encode response
    * \param $data Response data to encode and return
    * \param $status The HTTP status code for the response
    * \returns json-encoded response data
    */
   protected function response($data, $status = 200)
   {
      // set status header
      header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));

      // log the request status
      $msg = "Status: $status";
      Logger::log("%s\n", $msg);

      // dump the data to the log if the request failed
      if($status != 200)
      {
         ob_start();
         var_dump($data);
         $str = ob_get_contents();
         ob_end_clean();
         Logger::log("Data: %s", $str);
      }

      // return response data as json-encoded string
      return json_encode($data);
   }

   /**
    * \brief Sanitize input data
    * \param $data Input data to be sanitized
    * \param $type Data type for input data if not array
    * \returns Sanitized input data
    */
   private function sanitizeData($data, $type=null)
   {
      $sanitized_data = array();
      if (is_array($data))
      {
         foreach ($data as $k => $v)
         {
            $sanitized_data[$k] = $this->sanitizeData($v, $this->_types[$k]);
         }
      }
      else
      {
         $sanitized_data = trim(strip_tags($data));
         switch($type)
         {
            case 's':
               $sanitized_data = $this->_conn->escapeString($sanitized_data);
               break;
            case 'n':
               $sanitized_data = (int)$sanitized_data;
               break;
            case 'f':
               $sanitized_data = (float)$sanitized_data;
               break;
            case 'd':
               $d = date_parse($sanitized_data);
               $sanitized_data = sprintf("%04d-%02d-%02d",
                                         $d['year'],
                                         $d['month'],
                                         $d['day']);
               break;
            case 't':
               $t = date_parse($sanitized_data);
               $sanitized_data = sprintf("%02d:%02d:%02d",
                                         $t['hour'],
                                         $t['minute'],
                                         $t['second']);
               break;
            case 'dt':
               $dt = date_parse($sanitized_data);
               $sanitized_data = sprintf("%04d-%02d-%02d %02d:%02d:%02d",
                                         $dt['year'],
                                         $dt['month'],
                                         $dt['day'],
                                         $dt['hour'],
                                         $dt['minute'],
                                         $dt['second']);
               break;
         }
      }
      return $sanitized_data;
   }

   /**
    * \brief Get the HTTP status string for a status code
    * \param $code The HTTP status code
    * \returns Text associated with HTTP status code
    */
   protected function requestStatus($code)
   {
      $status = array
      (
         200 => 'OK',
         401 => 'Unauthorized',
         404 => 'Not Found',   
         405 => 'Method Not Allowed',
         500 => 'Internal Server Error',
      ); 
      return ($status[$code])?$status[$code]:$status[500]; 
   }

   /**
    * \brief Get request form data
    * \param $varName Name of request variable to get
    * \param $default Default value if request variable is empty
    * \param $isSet Indicates if variable was set
    */
   protected function getRequestVariable($varName, $default, &$isSet=null)
   {
      $ret = $default;
      if(array_key_exists($varName, $this->_request))
      {
         $ret = $this->_request[$varName];
         if($isSet != null)
         {
            $isSet = true;
         }
      }
      else
      {
         if($isSet != null)
         {
            $isSet = false;
         }
      }
      return $ret;
   }

   /**
    * \brief Set query variables from URL query
    * \param $queryVars The list of variables to received on request
    */
   public function setQueryVariables($queryVars)
   {
      $this->_query = $queryVars;
   }

   /**
    * \brief Retrieve query variable
    * \param $varName Name of variable to retrieve
    * \returns Value of variable (mixed) or null
    */
   protected function getQueryVariable($varName)
   {
      $ret = null;

      $ret = $this->_query[$varName] ?? null;

      return $ret;
   }

   public function getContent()
   {
      return $this->_file;
   }
}
