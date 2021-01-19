<?php
namespace wrxswoole\Core\Component;

use EasySwoole\Component\Di;
use EasySwoole\Component\Singleton;

class CoreDi
{
    use Singleton;
    private $container = array();

    public function set($key, $obj,...$arg):void
    {
        /*
         * 注入的时候不做任何的类型检测与转换
         * 由于编程人员为问题，该注入资源并不一定会被用到
         */
        $this->container[$key] = array(
            "obj"=>$obj,
            "params"=>$arg,
        );
    }

    function delete($key):void
    {
        unset( $this->container[$key]);
    }

    function clear():void
    {
        $this->container = array();
    }

    /**
     *
     * @param
     *            $key
     * @return null
     * @throws \Throwable
     */
    function get($key)
    {
        if (isset($this->container[$key])) {
            $obj = $this->container[$key]['obj'];
            $params = $this->container[$key]['params'];
            if (is_object($obj) || is_callable($obj)) {
                return $obj;
            } else {
                return $obj;
            }
        } else {
            return null;
        }
    }
}

?>