<?php

namespace DigTech\EventManager;

/**
 * \class EventHandler
 * \ingroup Services
 * \brief The class is the base class for all event handlers
 */
class EventHandler
{
    /**
     * \brief Object constructor
     */
    public function __construct()
    {
    }

    /**
     * \brief Initialize handler
     */
    public function start()
    {
    }

    /**
     * \brief Process event
     * \param $event Event data to be processed
     */
    public function process(&$event)
    {
    }

    /**
     * \brief Cleanup handler
     */
    public function finish()
    {
    }
}

?>