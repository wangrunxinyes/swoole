<?php
namespace App\Module\Demo\Model;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class ParentClass
{

    private $private = [];

    protected $protect = [];

    function change()
    {
        $this->private = [
            "test" => "parent"
        ];

        $this->protect = [
            "test" => "parent"
        ];
    }

    function getPrivate()
    {
        return $this->private;
    }

    function getProtect()
    {
        return $this->protect;
    }
}