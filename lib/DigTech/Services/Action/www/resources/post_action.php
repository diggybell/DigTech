<?php

use DigTech\REST\Resource as Resource;
use DigTech\Logging\Logger as Logger;
use DigTech\Database\Record as Record;

/**
 * \class post_action
 * \ingroup Services
 * \brief This class provides the REST API to allow creation of new actions.
 * It should be called by all applications and systems that need to create actions.
 */

class post_action extends Resource
{
    public function __construct($request, $schema='actionmgr')
    {
        parent::__construct($request, $schema);
    }
 
    protected function getClass($class)
    {
        $ret = 0;

        $sql = sprintf("SELECT class_seq FROM action_class WHERE class_code = '%s'", $class);
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

        $sql = sprintf("SELECT performer_seq FROM action_performer WHERE performer_code = '%s'", $performer);
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
        $action = json_decode($json);
        if(is_object($action))
        {
            $classSeq = $this->getClass($action->action->class);
            $performerSeq = $this->getPerformer($action->action->performer);

            if($classSeq > 0 && $performerSeq > 0)
            {
                $recAction = new Record($this->_conn, 'action_log', ['action_seq' => 0]);

                $recAction->set('class_seq', $classSeq);
                $recAction->set('performer_seq', $performerSeq);

                $recAction->set('action_timestamp', $action->action->timestamp);
                $recAction->set('action_payload', $json);

                if($recAction->insert())
                {
                    $status = 'Success';
                }
                else
                {
                    Logger::error("Unable to insert action into database\n");
                }
            }
            else
            {
                Logger::error("Invalid Class or Performer specified (%s/%s)\n", $action->action->class, $action->action->performer);
            }
        }
        else
        {
            Logger::error("Invalid JSON object\n");
        }

        return ['status'=>$status, 'data'=>$data];
    }
}

?>
