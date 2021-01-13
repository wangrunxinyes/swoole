<?php
namespace wrxswoole\Core\Model\Traits;

use EasySwoole\Component\Di;

/**
 * 
 * @author WANG RUNXIN
 *
 */

trait DiSingleTon
{

    public static function getInstanceKey(): string
    {
        return self::INSTANCE;
    }

    public static function getInstance($new = null)
    {
        $instance = Di::getInstance()->get(self::getInstanceKey());
        
        if (is_null($instance)) {
            return self::createNew();
        }
        
        return $instance;
    }

    public static function createNew($new = null)
    {
        if (! is_null($new)) {
            Di::getInstance()->set(self::getInstanceKey(), $new);
            return $new;
        }
        
        $model = new self();
        
        Di::getInstance()->set(self::getInstanceKey(), $model);
        
        return $model;
    }
}

?>