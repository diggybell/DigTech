<?php

/**
 * \class DigTechAutoloader
 * \brief This class implements an autoloader that is similar to the PSR-4 standard.
 * \todo Adapt to be PSR-4 compliant
 */
class DigTechAutoloader
{
    /**
     * \brief Object constructor which registers the autoload handler.
     */
    public function __construct()
    {
        spl_autoload_register(array($this, 'loader'));
    }

    /**
     * \brief This is the autoload handler called for DigTech classes
     * \param $className The name of the class to be loaded
     * \todo This method needs error handling improvements
     */
    private function loader($className)
    {
        $prefix = 'DigTech\\';
        $base_dir = __DIR__ . '/';

        // check for DigTech class
        $len = strlen($prefix);
        if (strncmp($prefix, $className, $len) !== 0)
        {
            return;
        }

        $file = $base_dir . str_replace('\\', '/', $className) . '.php';
        if (file_exists($file))
        {
            include_once($file);
        }
        else
        {
            throw new \Exception("Unable to load class $className.");
        }
    }
}

// Create the autoloader object
///< \todo some protections need to be put around this
if(!isset($dtal))
{
    $dtal = new DigTechAutoloader();
}
