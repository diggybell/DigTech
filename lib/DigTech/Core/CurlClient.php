<?php

namespace DigTech\Core;

/**
   \class CurlClient
   \ingroup Shared
   \brief Object interface for cURL library
 */
class CurlClient
{
   private $_curl_handle;           ///< cURL library handle
   private $_curl_cookie_store;     ///< Filename for cookie database
   private $_last_result_buffer;    ///< Last result data
   private $_last_result_code;      ///< Last HTTP result code
   private $_errno;                 ///< cURL errno
   private $_error;                 ///< cURL error string
   private $_return_header;         ///< Return HTTP header flag
   private $_http_headers = array();///< Array of additional headers to send
   private $_use_ssl;               ///< Flag to indicate if SSL should be enforced

   /**
      \brief Object constructor
      \param $cookie_store Name of cookie storage file, or null for no cookie support
    */
   function __construct($cookie_store=null)
   {
      $this->_curl_handle = null;
      $this->_curl_cookie_store = $cookie_store;
      $this->_use_ssl = true;
      $this->setup();
   }

   /**
      \brief Object destructor
    */
   function __destruct()
   {
      $this->cleanup();
   }

   /**
      \brief Active/deactive HTTP headers being returned with response
      \param $active True or false to control returning or not returning headers
    */
   function setHeaders($active=true)
   {
      $this->_return_header = $active;
   }

   /**
      \brief Add a custom header to send with the request
      \param $header Name of header
      \param $value Value of header
    */
   function addHeader($header, $value)
   {
      $this->_http_headers[$header] = $value;
   }

   /**
      \brief Get the custom headers to send
    */
   function getHeaders()
   {
      $ret = array();
      foreach($this->_http_headers as $header => $value)
      {
         $ret[] = "$header: $value";
      }
      return $ret;
   }

   /**
      \brief Setup the cURL environment
      \param $use_ssl Set to true to disable ssl certificate checks
    */
   function setup($use_ssl=false)
   {
      $this->_curl_handle = curl_init();

      if($this->_curl_cookie_store !== null)
      {
         curl_setopt($this->_curl_handle, CURLOPT_COOKIEJAR, $this->_curl_cookie_store);
         curl_setopt($this->_curl_handle, CURLOPT_COOKIEFILE, $this->_curl_cookie_store);
         curl_setopt($this->_curl_handle, CURLOPT_COOKIESESSION, 1);
      }
      curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, 1);
   }

   /**
      \brief Cleanup the cURL environment
    */
   function cleanup()
   {
      if($this->_curl_handle != null)
      {
         curl_close($this->_curl_handle);
         $this->_curl_handle = null;
      }
   }

   /**
      \brief Enable/disable SSL
      \param $use_ssl
    */
   function enableSSL($use_ssl=true)
   {
      $this->_use_ssl = $use_ssl;
   }

   /**
      \brief Set SSL options for a request
    */
   function setSSLOptions()
   {
      if(!$this->_use_ssl)
      {
         curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
         curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYPEER, false);
      }
   }

   /**
      \brief Execute an HTTP GET and retrieve the result
      \returns Result of GET
    */
   function get($url, $fields)
   {
      if(is_array($fields))
      {
         $fieldstr = http_build_query($fields);
         $url .= '?' . $fieldstr;
      }

      $this->setupRequest($url);
      curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, null);
      curl_setopt($this->_curl_handle, CURLOPT_HTTPGET, true);
      $this->execRequest();

      return $this->getResult();
   }

   /**
      \brief Execute an HTTP POST and retrieve the result
      \returns Result of POST
    */
   function post($url, $fields)
   {
      if(is_array($fields))
      {
         $fieldstr = http_build_query($fields);
      }
      elseif($fields !== null)
      {
         $fieldstr = $fields;
      }
      else
      {
         $fieldstr = '';
      }

      $this->setupRequest($url);
      curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, null);
      curl_setopt($this->_curl_handle, CURLOPT_POST, true);
      curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $fieldstr);
      $this->execRequest();

      return $this->getResult();
   }

   /**
      \brief Execute an HTTP PUT and retrieve the result
      \returns Result of PUT
    */
   function put($url, $fields)
   {
      if(is_array($fields))
      {
         $fieldstr = http_build_query($fields);
      }
      elseif($fields !== null)
      {
         $fieldstr = $fields;
      }
      else
      {
         $fieldstr = '';
      }

      $this->setupRequest($url);
      curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, "PUT");
      curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $fieldstr);
      $this->execRequest();

      return $this->getResult();
   }

   /**
      \brief Execute an HTTP DELETE and retrieve the result
      \returns Result of DELETE
    */
   function delete($url, $fields)
   {
      if(is_array($fields))
      {
         $fieldstr = http_build_query($fields);
         $url .= '?' . $fieldstr;
      }
      elseif($fields !== null)
      {
         $fieldstr = $fields;
      }
      else
      {
         $fieldstr = '';
      }

      $this->setupRequest($url);
      curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $fieldstr);
      $this->execRequest();
      
      return $this->getResult();
   }

   /**
      \brief Setup request
      \param $url The url for the request
    */
   protected function setupRequest($url)
   {
      curl_setopt($this->_curl_handle, CURLOPT_HEADER, $this->_return_header);
      curl_setopt($this->_curl_handle, CURLOPT_URL, $url);
      curl_setopt($this->_curl_handle, CURLOPT_HTTPHEADER, $this->getHeaders());

      $this->setSSLOptions();
   }

   /**
      \brief Execute request
    */
   protected function execRequest()
   {
      $this->_last_result_buffer = curl_exec($this->_curl_handle);
      $this->_last_result_code   = curl_getinfo($this->_curl_handle, CURLINFO_HTTP_CODE);

      $this->_errno = curl_errno($this->_curl_handle);
      $this->_error = curl_error($this->_curl_handle);
   }

   /**
      \brief Get the result of the last HTTP request
      \returns Result of last GET/POST
    */
   function getResult()
   {
      return $this->_last_result_buffer;
   }

   /**
      \brief Get the result code of the last HTTP request
      \returns Result code of last GET/POST
    */
   function getResultCode()
   {
      return $this->_last_result_code;
   }

   /**
      \brief Get the error result from the last request
      \returns cURL errno
    */
   function errno()
   {
      return $this->_errno;
   }

   /**
      \brief Get the error string from the last request
      \returns cURL error string
    */
   function error()
   {
      return $this->_error;
   }
}
