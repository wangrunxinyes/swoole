<?php
namespace wrxswoole\Core\Model\Traits;

use Swoole\Coroutine;

/**
 *
 * @author WANG RUNXIN
 *        
 */
trait CoroutineSafeSingleTon
{

    private static $instance = [];

    static function getInstance(...$args)
    {
        return self::getStatic(...$args);
    }

    static function getStatic(...$args)
    {
        $cid = Coroutine::getCid();
        if (! isset(self::$instance[$cid])) {
            self::$instance[$cid] = new static(...$args);
            /*
             * 兼容非携程环境
             */
            if ($cid > 0) {
                Coroutine::defer(function () use ($cid) {
                    $core = self::$instance[$cid];
                    if (is_object($core)) {
                        if (method_exists($core, 'before_destroy')) {
                            $core->before_destroy();
                        }
                    }
                    unset($core);
                    unset(self::$instance[$cid]);
                });
            }
        }
        return self::$instance[$cid];
    }

    static function hasInstance(): bool
    {
        $cid = Coroutine::getCid();
        if ($cid > 0) {
            return isset(self::$instance[$cid]);
        }
        return false;
    }

    static function getCoroutineId(): string
    {
        return Coroutine::getCid();
    }

    static function setInstance($instance)
    {
        $cid = Coroutine::getCid();
        self::$instance[$cid] = $instance;

        if ($cid > 0) {
            Coroutine::defer(function () use ($cid) {
                unset(self::$instance[$cid]);
            });
        }

        return self::$instance[$cid];
    }

    function before_destroy()
    {
        return;
    }

    function destroy(int $cid = null)
    {
        if ($cid === null) {
            $cid = Coroutine::getCid();
        }

        $core = self::$instance[$cid];
        if (is_object($core)) {
            if (method_exists($core, before_destroy)) {
                $core->before_destroy();
            }
        }
        unset($core);
        unset(self::$instance[$cid]);
    }
}

?>