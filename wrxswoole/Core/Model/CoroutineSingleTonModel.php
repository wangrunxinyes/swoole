<?php
namespace wrxswoole\Core\Model;

use wrxswoole\Core\Model\Traits\CoroutineSafeSingleTon;
use wrxswoole\Core\Model\Traits\SingleTonTest;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class CoroutineSingleTonModel
{

    use CoroutineSafeSingleTon;
    use SingleTonTest;

    function before_destroy()
    {
        return;
    }
}

?>