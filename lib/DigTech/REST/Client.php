<?php

namespace DigTech\REST;

//include_once('Logger.php');
//include_once('CurlClient.php');

use \DigTech\Core\CurlClient as CurlClient;
use \DigTech\Logging\Logger as Logger;

/**
   \class Client
   \ingroup API
   \brief Implementation for REST API client
 */
class Client
{
   protected $_curl;       ///< cURL client
   protected $_url;        ///< URL for REST service
   protected $_param;      ///< URL parameters (GET and DELETE)
   protected $_username;   ///< User name
   protected $_password;   ///< Password
   protected $_token;      ///< User token
   protected $_appToken;   ///< Application token

   /**
      \brief Object constructor
      \param $url URL for REST service
    */
   public function __construct($url=null)
   {
      $this->_curl = new CurlClient(null);
      $this->_curl->enableSSL(false);

      if($url !== null)
      {
         $this->setURL($url);
      }
   }

   /**
      \brief Set URL
      \param $url URL
    */
   public function setURL($url)
   {
      $this->_url = $url;
   }

   /**
      \brief Set URL parameter
      \param $param Parameter to append to URL
    */
   public function setParam($param)
   {
      $this->_param = $param;
   }

   /**
      \brief Set user name
      \param $username User name
    */
   public function setUserName($username)
   {
      $this->_username = $username;
   }

   /**
      \brief Set password
      \param $password Password
    */
   public function setPassword($password)
   {
      $this->_password = $password;
   }

   /**
      \brief Set token
      \param $token User token
    */
   public function setToken($token)
   {
      $this->_token = $token;
   }

   /**
      \brief Set application token
      \param $appToken Application token
    */
   public function setAppToken($appToken)
   {
      $this->_appToken = $appToken;
   }

   /**
      \brief Create Authorization header
      \returns Authorization header value
    */
   public function getAuthorizationHeader()
   {
      return 'Basic ' .
             base64_encode($this->_username . ':' .
                           $this->_password . ':' .
                           $this->_token . ':' .
                           $this->_appToken);
   }

   /**
      \brief Call REST service
      \param $method HTTP method to use
      \param $data Data to be included with request
    */
   public function callService($method, $data=null)
   {
      $this->_curl->addHeader('Authorization', $this->getAuthorizationHeader());

      $url = $this->_url;
      if(substr($url, -1) != '/')
      {
         $url .= '/';
      }
      $url .= $this->_param;

      switch(strtolower($method))
      {
         case 'get':
            $response = $this->_curl->get($url, $data);
            break;
         case 'post':
            $response = $this->_curl->post($url, $data);
            break;
         case 'put':
            $response = $this->_curl->put($url, $data);
            break;
         case 'delete':
            $response = $this->_curl->delete($url, $data);
            break;
      }

      if($this->_curl->getResultCode() != 200)
      {
         Logger::log("REST  : Result: %d - Error: %s - ErrNo: %d - URL: %s",
                     $this->_curl->getResultCode(),
                     $this->_curl->error(),
                     $this->_curl->errno(),
                     $this->_url);
      }

      return $response;
   }
}
