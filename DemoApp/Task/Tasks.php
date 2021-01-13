<?php
namespace App\Task;

use EasySwoole\EasySwoole\Swoole\EventRegister;

class Tasks
{

    static function All()
    {
        return [
            EventRegister::onWorkerStart => [
                function ($server, $workerId) {
                    // 例如在第一个进程 添加一个10秒的定时器
                    if ($workerId == 0) {
                        \EasySwoole\Component\Timer::getInstance()->loop(10 * 1000, function () {
                            // 从数据库，或者是redis中，去获取下个就近10秒内需要执行的任务
                            // 例如:2秒后一个任务，3秒后一个任务 代码如下
                            \EasySwoole\Component\Timer::getInstance()->after(2 * 1000, function () {
//                                 print_r(2);
                            });
                            \EasySwoole\Component\Timer::getInstance()->after(3 * 1000, function () {
                                // 为了防止因为任务阻塞，引起定时器不准确，把任务给异步进程处理
//                                 print_r(3);
                            });
                        });
                    }
                }
            ]
        ];
    }
}

?>