<?php
namespace wrxswoole\Core\Database\Component;

use wrxswoole\Core\Component\BaseObject;

class Expression extends BaseObject implements ExpressionInterface
{

    /**
     *
     * @var string the DB expression
     */
    public $expression;

    /**
     *
     * @var array list of parameters that should be bound for this expression.
     *      The keys are placeholders appearing in [[expression]] and the values
     *      are the corresponding parameter values.
     */
    public $params = [];

    /**
     * Constructor.
     *
     * @param string $expression
     *            the DB expression
     * @param array $params
     *            parameters
     * @param array $config
     *            name-value pairs that will be used to initialize the object properties
     */
    public function __construct($expression, $params = [], $config = [])
    {
        $this->expression = $expression;
        $this->params = $params;
        parent::__construct($config);
    }

    /**
     * String magic method.
     *
     * @return string the DB expression.
     */
    public function __toString()
    {
        return $this->expression;
    }
}
