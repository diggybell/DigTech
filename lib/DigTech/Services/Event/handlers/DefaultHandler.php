<?php

use DigTech\EventManager\EventHandler as Handler;
use DigTech\Logging\Logger as Logger;

class DefaultHandler extends Handler
{
    public function __construct()
    {
        Logger::log("DefaultHandler->__construct()\n");
    }

    public function start()
    {
        Logger::log("DefaultHandler->start()\n");
    }

    public function process(&$event)
    {
        Logger::log("DefaultHandler->process()\n");
        Logger::log("Source: %s - Performer: %s - Class: %s - Event: %s\n",
                    $event->event->source,
                    $event->event->performer,
                    $event->event->class,
                    $event->event->details->event);
    }

    public function finish()
    {
        Logger::log("DefaultHandler->finish()\n");
    }
}
