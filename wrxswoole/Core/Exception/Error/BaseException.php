<?php
namespace wrxswoole\Core\Exception\Error;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class BaseException extends \Exception
{

    public $hint = null;

    

    function __construct($hint, $message, $code = null, $previous = null)
    {
        $this->hint = $hint;
        parent::__construct($message, $code, $previous);
    }
}

?>