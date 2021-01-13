<?php
namespace wrxswoole\Core\Database\Component;

final class PdoValue implements ExpressionInterface
{

    /**
     *
     * @var mixed
     */
    private $value;

    /**
     *
     * @var int One of PDO_PARAM_* constants
     * @see https://secure.php.net/manual/en/pdo.constants.php
     */
    private $type;

    /**
     * PdoValue constructor.
     *
     * @param
     *            $value
     * @param
     *            $type
     */
    public function __construct($value, $type)
    {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
