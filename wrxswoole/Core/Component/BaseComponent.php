<?php

namespace wrxswoole\Core\Component;

abstract class BaseComponent
{

    /**
     * init
     *
     * @return static
     */
    abstract static function init();
    abstract static function getTag(): string;
}
