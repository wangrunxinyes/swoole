<?php
namespace wrxswoole\Core\Model;

use wrxswoole\Core\Model\Traits\ContextSingleTon;
use wrxswoole\Core\Model\Traits\SingleTonTest;

/**
 * 
 * @author WANG RUNXIN
 *
 */
class ContextSingleTonModel
{

    const INSTANCE = 'ContextSingleTon_Instance';

    use ContextSingleTon;
    use SingleTonTest;
}

?>