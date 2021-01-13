<?php
namespace wrxswoole\Core\Model;

use EasySwoole\Component\Singleton;
use wrxswoole\Core\Model\Traits\SingleTonTest;

/**
 * 
 * @author WANG RUNXIN
 *
 */
class SingleTonModel
{

    use Singleton;
    use SingleTonTest;
}

?>