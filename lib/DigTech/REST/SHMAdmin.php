<?php

class SHMAdmin
{
    protected $_resource;
    protected $_mutex;
    protected $_sharemem;

    public function __construct()
    {
        $this->setupStatistics();
    }
    protected function getSharedMemory()
    {
        $data = [];

        if($this->_mutex->lock(3000))
        {
            $json = $this->_sharemem->read(0);
            printf("getSharedMemory() - [%s] - [%s = %d]\n", $json, $json[0], ord($json[0]));

            $json = str_replace("\0", '', $json);
            $data = json_decode($json, true);
            printf("getSharedMemory() - [%s] - %s\n", print_r($data, true), is_array($data));

            $this->_mutex->unlock();
        }
        else
        {
            throw new \Exception("Unable to get shared memory lock");
        }

        return $data;
    }

    protected function putSharedMemory($data)
    {
        if($this->_mutex->lock(3000))
        {
            $json = json_encode($data);
            printf("putSharedMemory() - [%s]\n", $json);
            $json = str_pad($json, $this->_sharemem->size(), "\0");

            $this->_sharemem->write($json, 0);

            $this->_mutex->unlock();
        }
        else
        {
            throw new \Exception("Unable to get shared memory lock");
        }
    }

    protected function setupStatistics()
    {
        $this->_mutex = new \SyncMutex('DigTechStatisticsMutex');
        if($this->_mutex->lock(3000))
        {
            $this->_sharemem = new \SyncSharedMemory('DigTechSharedMemory', 8192);

            $data = $this->getSharedMemory();

printf("Data Read: %s\n", var_export($data, true));

            if($this->_sharemem->first())
            //if(!is_array($data))
            //if($data === null)
            {
                printf("Resetting Global Statistics\n");
                $data = [];
                $data['global']['requests'] = 0;
                $data['global']['success'] = 0;
                $data['global']['errors'] = 0;
                $this->putSharedMemory($data);
            }
            $this->_mutex->unlock();
        }
    }

    protected function updateRequestStatistics()
    {
        $stats = $this->getSharedMemory();
        if(!isset($stats[$this->_resource]))
        {
            $stats[$this->_resource] = [];
            $stats[$this->_resource]['requests'] = 0;
            $stats[$this->_resource]['success'] = 0;
            $stats[$this->_resource]['errors'] = 0;
        }
        $stats['global']['requests']++;
        $stats[$this->_resource]['requests']++;
        $this->putSharedMemory($stats);
    }
}

$c = new SHMAdmin();
fgets(STDIN);
