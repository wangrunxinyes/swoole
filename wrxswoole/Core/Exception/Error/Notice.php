<?php
namespace wrxswoole\Core\Exception\Error;

/**
 *
 * non-thrown exception
 *
 * @author WANG RUNXIN
 *
 */
class Notice extends BaseException
{
    
    function __construct($hint, $message = "notice message", $code = null, $previous = null)
    {
        return parent::__construct($hint, $message, $code, $previous);
    }
}

?>