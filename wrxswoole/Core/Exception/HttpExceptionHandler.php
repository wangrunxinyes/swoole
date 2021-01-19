<?php
namespace wrxswoole\Core\Exception;

use wrxswoole\Core\Exception\Error\MutipleException;
use EasySwoole\Http\Request;
use wrxswoole\Core\Component\CoreDi;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class HttpExceptionHandler
{

    static function Handle($throwable, $request, $response)
    {
        BaseExceptionHandler::getInstance($throwable, $response)->end();
    }
}