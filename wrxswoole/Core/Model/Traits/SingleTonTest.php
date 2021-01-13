<?php
namespace wrxswoole\Core\Model\Traits;

/**
 * 
 * @author WANG RUNXIN
 *
 */
trait SingleTonTest
{

    private $key = "init";

    public function setKey(string $key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }
}

?>