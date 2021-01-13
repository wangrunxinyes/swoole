<?php
namespace wrxswoole\Core\Exception\Error;

/**
 *
 * non-thrown exception
 *
 * @author WANG RUNXIN
 *        
 */
class DbException extends BaseException
{

    function __construct($hint, $message = "debug hint", $code = null, $previous = null)
    {
        return parent::__construct($hint, $message, $code, $previous);
    }
}

?>