<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace wrxswoole\Core\Database\Component\Mysql;

use wrxswoole\Core\Database\Component\ExpressionBuilderInterface;
use wrxswoole\Core\Database\Component\ExpressionBuilderTrait;
use wrxswoole\Core\Database\Component\ExpressionInterface;
use wrxswoole\Core\Database\Component\Query;

/**
 * Class JsonExpressionBuilder builds [[JsonExpression]] for MySQL DBMS.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class JsonExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;

    const PARAM_PREFIX = ':qp';

    /**
     *
     * {@inheritdoc}
     * @param ExpressionInterface $expression
     *            the expression to be built
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $value = $expression->getValue();

        if ($value instanceof Query) {
            list ($sql, $params) = $this->queryBuilder->build($value, $params);
            return "($sql)";
        }

        $placeholder = static::PARAM_PREFIX . count($params);
        $params[$placeholder] = \PHPUnit\Util\Json::encode($value);

        return "CAST($placeholder AS JSON)";
    }
}
