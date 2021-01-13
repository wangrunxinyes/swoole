<?php
namespace wrxswoole\Core\Validator;

/**
 *
 * @author WANG RUNXIN
 *        
 */
trait ValidatorTrait
{

    /**
     * mix-type value for validate;
     */
    public $value = null;

    public function setValue($value)
    {
        $this->value = $value;
    }
}

?>