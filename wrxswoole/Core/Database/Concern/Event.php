<?php

namespace wrxswoole\Core\Database\Concern;


trait Event
{

    /* 回调事件 */
    protected $onQuery;

    public function onQuery(callable $call)
    {
        $this->onQuery = $call;
        return $this;
    }

    /**
     * 调用事件
     * @param $eventName
     * @param array $param
     * @return bool|mixed
     */
    protected function callEvent($eventName, ...$param)
    {
        if(method_exists(static::class, $eventName)){
            return call_user_func([static::class, $eventName], $this, ...$param);
        }
        return true;
    }
}