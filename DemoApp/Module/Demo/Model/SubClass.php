<?php
namespace App\Module\Demo\Model;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class SubClass extends ParentClass
{

    private $private = [];

    function getPrivate()
    {
        return $this->private;
    }

    function changeSub()
    {
        $this->private = [
            "test" => "sub"
        ];

        $this->protect = [
            "test" => "sub"
        ];
    }
}