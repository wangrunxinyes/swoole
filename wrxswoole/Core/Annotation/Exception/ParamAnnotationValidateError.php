<?php
namespace wrxswoole\Core\Annotation\Exception;

use EasySwoole\Validate\Validate;
use wrxswoole\Core\Exception\Error\Notice;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class ParamAnnotationValidateError extends Notice
{

    /**
     *
     * @var Validate
     */
    private $validate;

    /**
     *
     * @return Validate
     */
    public function getValidate(): ?Validate
    {
        return $this->validate;
    }

    /**
     *
     * @param Validate $validate
     */
    public function setValidate(Validate $validate): void
    {
        $this->validate = $validate;
    }
}