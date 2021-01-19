<?php

namespace App\Component;

use wrxswoole\Core\Component\BaseComponent;

class IdentityTest extends BaseComponent
{
    private $_id;
    static function init()
    {
        return new IdentityTest();
    }

    static function getTag(): string
    {
        return "test";
    }

    function __construct()
    {
        $this->_id = uniqid();
    }

    function log(){
        // print_r($this->_id);
    }
}