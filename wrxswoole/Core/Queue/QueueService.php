<?php
namespace wrxswoole\Core\Queue;

use wrxswoole\Core\Process\Consumer as ConsumerProcess;
use EasySwoole\EasySwoole\ServerManager;
;

class QueueService
{

    static function register()
    {

        /**
         * create consumer;
         */
        ServerManager::getInstance()->addProcess((new ConsumerProcess()), "customer_1");
        ServerManager::getInstance()->addProcess((new ConsumerProcess()), "customer_2");
        ServerManager::getInstance()->addProcess((new ConsumerProcess()), "customer_3");
        ServerManager::getInstance()->addProcess((new ConsumerProcess()), "customer_4");
        ServerManager::getInstance()->addProcess((new ConsumerProcess()), "customer_5");
    }
}

?>