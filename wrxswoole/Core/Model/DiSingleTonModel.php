<?php
namespace wrxswoole\Core\Model;

use wrxswoole\Core\Model\Traits\DiSingleTon;
use wrxswoole\Core\Model\Traits\SingleTonTest;

/**
 * 
 * @author WANG RUNXIN
 *
 */
class DiSingleTonModel
{

    const INSTANCE = 'TheadSafeSingleTon_Instance';

    use DiSingleTon;
    use SingleTonTest;
}

?>