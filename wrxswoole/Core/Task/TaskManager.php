<?php
namespace wrxswoole\Core\Task;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Component\Singleton;

class TaskManager
{
    use Singleton;

    /**
     *
     * @var EventRegister
     */
    private $register = null;

    function __construct(EventRegister $register)
    {
        $this->register = $register;
    }

    static function register(EventRegister $register, $tasks)
    {
        $manager = TaskManager::getInstance($register);
        $manager->addTask($tasks);
    }

    function addTask($tasks)
    {
        foreach ($tasks as $key => $funcs) {
            foreach ($funcs as $func) {
                $this->register->add($key, $func);
            }
        }
    }
}

?>