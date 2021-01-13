<?php
namespace wrxswoole\Core\Component;

use EasySwoole\Component\Di;

class CoreDi extends Di
{

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