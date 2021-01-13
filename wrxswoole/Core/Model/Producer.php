<?php
namespace wrxswoole\Core\Model;

use EasySwoole\Component\Singleton;
use EasySwoole\Component\WaitGroup;
use EasySwoole\Kafka\Kafka;
use EasySwoole\Kafka\Config\ProducerConfig;

class Producer
{
    use Singleton;

    private $kafka = null;

    function __construct()
    {
        $config = new ProducerConfig();
        $config->setMetadataBrokerList('127.0.0.1:9092');
        $config->setBrokerVersion('0.9.0');
        $config->setRequiredAck(1);

        $this->kafka = new kafka($config);
    }

    public function run()
    {}

    public function publish($data)
    {
        $kafka = $this->kafka;
        $result = [];

        $wait = new WaitGroup();

        $wait->add();
        go(function () use ($kafka, $data, &$result, $wait) {
            $result = $kafka->producer()->send($data);
            $wait->done();
        });

        $wait->wait();
        return $result;
    }
}