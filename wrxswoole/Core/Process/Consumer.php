<?php
namespace wrxswoole\Core\Process;

use App\App;
use App\Model\Customer;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Kafka\Kafka;
use EasySwoole\Kafka\Config\ConsumerConfig;

class Consumer extends AbstractProcess
{

    const PROCESSNAME = "CORE_CONSUMER";

    protected function run($arg)
    {
        go(function () {
            $config = new ConsumerConfig();
            $config->setRefreshIntervalMs(1000);
            $config->setMetadataBrokerList('127.0.0.1:9092');
            $config->setBrokerVersion('0.9.0');
            $config->setGroupId('test');

            $config->setTopics([
                'test'
            ]);
            $config->setOffsetReset('earliest');

            $kafka = new Kafka($config);
            // 设置消费回调
            $func = function ($topic, $partition, $message) {

                $result = [];
                
                App::getDb()->startTransaction();
                $model = Customer::create()->get([
                    "id" => 1
                ]);

                $result[] = [
                    "original_code" => $model->code
                ];

                $model->code = "normal_test";

                $result[] = [
                    "set_code_with_transacion" => $model->code
                ];

                $model->update();

                $result[] = [
                    "transaction_rollback" => App::getDb()->rollback()
                ];

                print_r([
                    "consumer_result" => [
                        "topic" => $topic,
                        "partition" => $partition,
                        "message" => $message,
                        "result" => $result,
                        "time" => date("Y-m-d H:i:s")
                    ]
                ]);
            };
            $kafka->consumer()->subscribe($func);
        });
    }
}

?>