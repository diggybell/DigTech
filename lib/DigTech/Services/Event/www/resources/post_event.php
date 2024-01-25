<?php

use DigTech\REST\Resource as Resource;
use DigTech\Logging\Logger as Logger;
use DigTech\Database\Record as Record;

/**
 * \class post_event
 * \ingroup Services
 * \brief This class provides the REST API to allow creation of new events.
 * It should be called by all applications and systems that need to create events.
 */

class post_event extends Resource
{
    public function __construct($request, $schema='eventmgr')
    {
        parent::__construct($request, $schema);
    }
 
    protected function getClass($class)
    {
        $ret = 0;

        $sql = sprintf("SELECT class_seq FROM event_class WHERE class_code = '%s'", $class);
        $res = $this->_conn->query($sql);
        if($res)
        {
            $row = $this->_conn->fetch($res);
            $this->_conn->freeResult($res);
            $ret = $row['class_seq'];
        }

        return $ret;
    }

    protected function getPerformer($performer)
    {
        $ret = 0;

        $sql = sprintf("SELECT performer_seq FROM event_performer WHERE performer_code = '%s'", $performer);
        $res = $this->_conn->query($sql);
        if($res)
        {
            $row = $this->_conn->fetch($res);
            $this->_conn->freeResult($res);
            $ret = $row['performer_seq'];
        }

        return $ret;
    }

    public function POST()
    {
        $status = 'Failed';
        $data = [];

        $json = $this->getContent();
        $event = json_decode($json);
        if(is_object($event))
        {
            $recEvent = new Record($this->_conn, 'event_log', ['event_seq' => 0]);

            $recEvent->set('class_code', $event->event->class);
            $recEvent->set('performer_code', $event->event->performer);

            $recEvent->set('event_timestamp', $event->event->timestamp);
            $recEvent->set('event_payload', $json);

            if($recEvent->insert())
            {
                $status = 'Success';
            }
            else
            {
                Logger::error("Unable to insert event into database\n");
            }
        }
        else
        {
            Logger::error("Invalid JSON object\n");
        }

        return ['status'=>$status, 'data'=>$data];
    }
}
