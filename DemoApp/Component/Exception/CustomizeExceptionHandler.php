<?php

namespace App\Component\Exception;

use wrxswoole\Core\Exception\Component\ExceptionHandler;

class CustomizeExceptionHandler extends ExceptionHandler
{

    function makeProduceResponse(): array
    {
        return [
            "handler" => CustomizeExceptionHandler::class,
            "dependencyInjection" => "\App\App()->dependencyInjection()"
        ];
    }
}
