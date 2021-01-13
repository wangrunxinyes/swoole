<?php
namespace wrxswoole\Core\Database\Model;

use App\App;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\ORM\Utility\PreProcess;
use EasySwoole\ORM\Utility\Schema\Table;
use wrxswoole\Core\Database\Concern\Attribute;
use wrxswoole\Core\Database\Concern\Event;
use wrxswoole\Core\Database\Concern\RelationShip;
use wrxswoole\Core\Database\Concern\TimeStamp;

/**
 * able to use Coroutine Safe/Unsafe Db manager;
 *
 * @author WANG RUNXIN
 *        
 */
class AbstractDbModel extends PublicDbModel
{

    use TimeStamp;
    use RelationShip;
    use Attribute;
    use Event;

    /**
     * add db changes audit
     *
     * @var bool
     */
    public $enableAudit = true;

    public function getCurrentDbConnectionName(): string
    {
        if ($this->tempConnectionName) {
            $connectionName = $this->tempConnectionName;
        } else {
            $connectionName = $this->connectionName;
        }

        return $connectionName;
    }

    /**
     *
     * @param bool $isCache
     * @return Table
     * @throws Exception
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $key = md5(static::class);
        if (isset(self::$schemaInfoList[$key]) && self::$schemaInfoList[$key] instanceof Table && $isCache == true) {
            return self::$schemaInfoList[$key];
        }
        if ($this->tempConnectionName) {
            $connectionName = $this->tempConnectionName;
        } else {
            $connectionName = $this->connectionName;
        }
        if (empty($this->tableName)) {
            throw new Exception("Table name is require for model " . static::class);
        }
        $tableObjectGeneration = new AdvancedTableObjectGeneration(App::getDb()->getConnection($connectionName), $this->tableName);
        $tableObjectGeneration->setTraceAble($this->enableAudit);
        $schemaInfo = $tableObjectGeneration->generationTable();
        self::$schemaInfoList[$key] = $schemaInfo;
        return self::$schemaInfoList[$key];
    }

    /**
     *
     * @param
     *            $attrName
     * @param
     *            $attrValue
     * @param bool $setter
     * @return bool
     * @throws \Exception
     */
    public function setAttr($attrName, $attrValue, $setter = true): bool
    {
        if (isset($this->schemaInfo()->getColumns()[$attrName])) {
            $col = $this->schemaInfo()->getColumns()[$attrName];
            $attrValue = is_null($attrValue) ? null : PreProcess::dataValueFormat($attrValue, $col);
            $method = 'set' . str_replace(' ', '', ucwords(str_replace([
                '-',
                '_'
            ], ' ', $attrName))) . 'Attr';
            if ($setter && method_exists($this, $method)) {
                $attrValue = $this->$method($attrValue, $this->data);
            }
            $this->data[$attrName] = $attrValue;
            return true;
        } else {
            $this->_joinData[$attrName] = $attrValue;
            return false;
        }
    }

    /**
     *
     * @param QueryBuilder $builder
     * @param bool $raw
     * @return mixed
     * @throws \Throwable
     */
    public function query(QueryBuilder $builder, bool $raw = false)
    {
        $start = microtime(true);
        $this->lastQuery = clone $builder;
        if ($this->tempConnectionName) {
            $connectionName = $this->tempConnectionName;
        } else {
            $connectionName = $this->connectionName;
        }
        try {
            $ret = null;
            if ($this->client) {
                $ret = App::getDb()->query($builder, $raw, $this->client, null, $this->enableAudit);
            } else {
                $ret = App::getDb()->query($builder, $raw, $connectionName, null, $this->enableAudit);
            }
            $builder->reset();
            $this->lastQueryResult = $ret;
            return $ret->getResult();
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            $this->reset();
            if ($this->onQuery && $this->enableAudit) {
                $temp = clone $builder;
                call_user_func($this->onQuery, $ret, $temp, $start);
            }
        }
    }

    protected static function onBeforeInsert($model)
    {
        /**
         *
         * @var AbstractDbModel $model
         */
        $primaryKey = $model->schemaInfo()->getPkFiledName();
        if (! empty($primaryKey) && !is_null($model->$primaryKey)) {
            // update;
            $rawArray = $model->toArray(true);
            $model->update($rawArray, [
                $primaryKey => $model->$primaryKey
            ]);
            
            return false;
        }
        return true;
    }
}

?>