<?php

include_once('../../../../autoload.php');
include_once('../../../../autoconfig.php');

use \DigTech\Logging\Logger as Logger;
use \DigTech\Database\MySQL as MyDB;
use \DigTech\Database\Record as Record;

$cfg = getGlobalConfiguration();

$db = new MyDB\Connection();

$config = $cfg->getSection('db-eventmgr');
$db->configure($config);

$totalEvents = 0;
$processedEvents = 0;

Logger::log("Event Processing Started\n");

if($cfg->getRunState() !== 'active')
{
    Logger::warning("Event processing stopped, host/system not active\n");
    exit(1);
}

if($db->connect())
{
    $recEvent = new Record($db, 'event_log', [ 'event_seq' => 0 ]);
    $sql = sprintf("SELECT event_seq FROM event_log WHERE event_processed IS NULL ORDER BY event_seq");
    $res = $db->query($sql);
    if($res)
    {
        while($row = $db->fetch($res))
        {
            $totalEvents++;
            $recEvent->set('event_seq', $row['event_seq']);
            if($recEvent->read())
            {
                printf("%s\n", $recEvent->get('event_payload'));

//                $recEvent->set('event_processed', date('Y-m-d H:i:s'));
                if($recEvent->update())
                {
                    $processedEvents++;
                }
                else
                {
                    Logger::error("Unable to update event record to processed\n");
                }
            }
            else
            {
                Logger::error("Unable to read event record\n");
            }
        }
        $db->freeResult($res);
    }
    else
    {
        Logger::error("Query failed to retrieve events\n");
    }
}
else
{
    Logger::error("Database Connection Failed\n");
}

Logger::log("Event Processing Completed: %d of %d processed\n", $processedEvents, $totalEvents);

?>