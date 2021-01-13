<?php
namespace wrxswoole\Core\Exception;

use wrxswoole\Core\Exception\Error\MutipleException;
use EasySwoole\Http\Request;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class HttpExceptionHandler
{

    static function Handle($throwable, $request, $response)
    {
        $handler = new ExceptionHandler($throwable, $response);
        $handler->end();
    }
}