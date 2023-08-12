<?php

namespace DigTech\Logging;

/**
 * \class Logger
 * \ingroup Logging
 * \brief This class provides logging support for errors, warnings, and debug information.
 */
class Logger
{
   protected static $_enabled = true;        ///< Flag to indicate if all logging is enabled
   protected static $_debugEnabled = true;   ///< Flag to indicate if debug logging is enabled
   protected static $_warningEnabled = true; ///< Flag to indicate if warning logging is enabled
   protected static $_errorEnabled = true;   ///< Flag to indicate if error logging is enabled
   protected static $_fileName = null;       ///< File name if logging is sent to file

   /**
    * \brief Object constructor
    */
   public function __construct()
   {
   }

   public static function configure($config)
   {
      self::setEnabled($config->enabled                ?? null);
      self::setDebugEnabled($config->debug_enabled     ?? null);
      self::setWarningEnabled($config->warning_enabled ?? null);
      self::setErrorEnabled($config->error_enabled     ?? null);
      self::setLogFile($config->file_name              ?? null);
   }

   /**
    * \brief Enable/disable all logging
    * \param $enabled Set to true to enable logging, false to disable logging
    */
   public static function setEnabled($enabled=true)
   {
      self::$_enabled = $enabled;
   }

   /**
    * \brief Enable/disable debug logging
    * \param $enabled Set to true to enable logging, false to disable logging
    */
   public static function setDebugEnabled($enabled=true)
   {
      self::$_debugEnabled = $enabled;
   }
   
   /**
    * \brief Enable/disable warning logging
    * \param $enabled Set to true to enable logging, false to disable logging
    */
   public static function setWarningEnabled($enabled=true)
   {
      self::$_warningEnabled = $enabled;
   }
   
   /**
    * \brief Enable/disable error logging
    * \param $enabled Set to true to enable logging, false to disable logging
    */
   public static function setErrorEnabled($enabled=true)
   {
      self::$_errorEnabled = $enabled;
   }
   
   /**
    * \brief Output a message to the log
    * \param $format A printf-stype format string followed by parameters
    */
   public static function log($format)
   {
      if(self::$_enabled)
      {
         $args = [];
   
         for($index = 1; $index < func_num_args(); $index++)
         {
            $args[$index - 1] = func_get_arg($index);
         }
   
         $message = vsprintf($format, $args);
   
         self::output($message);
      }
   }

   /**
    * \brief Output a debug message to the log with additiona diagnostic information
    * \param $format A printf-stype format string followed by parameters
    */
    public static function debug($format)
   {
      if(self::$_debugEnabled)
      {
         $args = [];
   
         for($index = 1; $index < func_num_args(); $index++)
         {
            $args[$index - 1] = func_get_arg($index);
         }
   
         $message = 'DEBUG: ' . vsprintf($format, $args);
   
         $trace = debug_backtrace();
         $stackTrace = '';
         foreach($trace as $frame)
         {
            $stackTrace .= sprintf("%s::%s called from %s (%d)\n",
                                   $frame['class']??'',
                                   $frame['function'],
                                   $frame['file'],
                                   $frame['line']);
         }
         $message .= $stackTrace;
   
         self::output($message);
      }
   }

   /**
    * \brief Output a warning message to the log
    * \param $format A printf-stype format string followed by parameters
    */
   public static function warning($format)
   {
      if(self::$_warningEnabled)
      {
         $args = [];
   
         for($index = 1; $index < func_num_args(); $index++)
         {
            $args[$index - 1] = func_get_arg($index);
         }
   
         $message = 'WARNING: ' . vsprintf($format, $args);
   
         self::output($message);
      }
   }

   /**
    * \brief Output an error message to the log with source/line information
    * \param $format A printf-stype format string followed by parameters
    */
   public static function error($format)
   {
      if(self::$_errorEnabled)
      {
         $args = [];
   
         for($index = 1; $index < func_num_args(); $index++)
         {
            $args[$index - 1] = func_get_arg($index);
         }
            
         $trace = debug_backtrace();
         $message = sprintf("ERROR: %s(%d) ",
                            basename($trace[0]['file']),
                            $trace[0]['line']);
         
         $message .= vsprintf($format, $args);

         self::output($message);
      }
   }

   /**
    * \brief Common output handler for all log messages
    * \param $message The message to be output
    */
    protected static function output($message)
   {
      if(self::$_enabled)
      {
         $msg = sprintf("%s - %s",
                        date('Y-m-d H:i:s'),
                        $message);
   
         if(self::$_fileName === null)
         {
            printf("%s", $msg);
         }
         else
         {
            $file = fopen(self::$_fileName, "a");
            if($file)
            {
               fprintf($file, "%s", $msg);
               fflush($file);
               fclose($file);
            }
         }
      }
   }

   public static function setLogFile($fileName = null)
   {
      self::$_fileName = $fileName;
   }
}

?>
