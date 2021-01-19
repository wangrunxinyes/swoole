<?php
namespace wrxswoole\Core\Exception;

use wrxswoole\Core\Exception\Component\ExceptionHandler;
use wrxswoole\Core\Trace\Tracker;

class BaseExceptionHandler extends ExceptionHandler{

    function makeProduceResponse(): array
    {
        return [
            "msg" => "an internal error has occurred.",
            "visitor" => Tracker::getInstance()->getTraceId()
        ];
    }
}