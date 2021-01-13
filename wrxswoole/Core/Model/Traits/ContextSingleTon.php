<?php
namespace wrxswoole\Core\Model\Traits;

use EasySwoole\Component\Context\ContextManager;

/**
 *
 * @author WANG RUNXIN
 *        
 */
trait ContextSingleTon
{

    public static function getInstanceKey(): string
    {
        return self::INSTANCE;
    }

    public static function getInstance($new = null)
    {
        $instance = ContextManager::getInstance()->get(self::getInstanceKey());

        if (is_null($instance)) {
            return self::createNew();
        }

        return $instance;
    }

    public static function createNew($new = null)
    {
        if (! is_null($new)) {
            ContextManager::getInstance()->set(self::getInstanceKey(), $new);
            return $new;
        }

        $model = new self();

        ContextManager::getInstance()->set(self::getInstanceKey(), $model);

        return $model;
    }
}

?>