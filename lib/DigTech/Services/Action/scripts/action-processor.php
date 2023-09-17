<?php

include_once('../../../../autoload.php');
include_once('../../../../autoconfig.php');

use \DigTech\Logging\Logger as Logger;
use \DigTech\Database\MySQL as MyDB;
use \DigTech\Database\Record as Record;

$cfg = getGlobalConfiguration();

$db = new MyDB\Connection();

$config = $cfg->getSection('db-actionmgr');
$db->configure($config);

if($db->connect())
{
    $recAction = new Record($db, 'action_log', [ 'action_seq' => 0 ]);
    $sql = sprintf("SELECT action_seq FROM action_log WHERE action_processed IS NULL ORDER BY action_seq");
    $res = $db->query($sql);
    if($res)
    {
        while($row = $db->fetch($res))
        {
            $recAction->set('action_seq', $row['action_seq']);
            if($recAction->read())
            {
                printf("%s\n", $recAction->get('action_payload'));

                $recAction->set('action_processed', date('Y-m-d H:i:s'));
                if($recAction->update())
                {
                }
                else
                {
                    Logger::error("Unable to update action record to processed\n");
                }
            }
            else
            {
                Logger::error("Unable to read action record\n");
            }
        }
        $db->freeResult($res);
    }
    else
    {
        Logger::error("Query failed to retrieve actions\n");
    }
}
else
{
    Logger::error("Database Connection Failed\n");
}

?>