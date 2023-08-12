<?php

include_once('../lib/autoload.php');
include_once('../lib/autoconfig.php');

// The configuration should be read early in the process
use \DigTech\Logging\Logger;

function main($argc, $argv)
{
    $config = getGlobalConfiguration();
    
    Logger::log("Standard log entry\n");
    Logger::debug("Something happened\n");
    Logger::warning("Something happened\n");
    Logger::error("Something happened\n");
}

main($argc, $argv);

Logger::log("*** All Disabled\n");
Logger::setEnabled(false);
main($argc, $argv);
Logger::setEnabled(true);

Logger::log("*** Debug Disabled\n");
Logger::setDebugEnabled(false);
main($argc, $argv);
Logger::setDebugEnabled(true);

Logger::log("*** Warning Disabled\n");
Logger::setWarningEnabled(false);
main($argc, $argv);
Logger::setWarningEnabled(true);

Logger::log("*** Error Disabled\n");
Logger::setErrorEnabled(false);
main($argc, $argv);
Logger::setErrorEnabled(true);

?>
