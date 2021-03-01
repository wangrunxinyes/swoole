<?php
namespace wrxswoole\Core\Database;

use App\App;
use App\Db\DBConfig;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\Db\Result;
use EasySwoole\ORM\Db\ClientInterface;

class Connection
{
    use ConnectionTrait;

    private $_connectionName;

    private $_log = true;

    static function create($connectionName = null): Connection
    {
        $connection = new Connection();
        $connection->setConnectionName($connectionName);
        return $connection;
    }

    /**
     *
     * @param QueryBuilder $builder
     * @param bool $raw
     * @param string|\EasySwoole\ORM\Db\ClientInterface $connection
     * @param float|null $timeout
     * @return Result
     * @throws \Exception
     * @throws \Throwable
     */
    function query(QueryBuilder $builder, bool $raw = false): Result
    {
        $trace = $this->_log ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10) : false;

        return App::getDb()->invoke(function (ClientInterface $client) use ($builder, $raw, $trace) {
            try {
                $start = microtime(true);
                $ret = $client->query($builder, $raw);
                if ($trace !== false) {
                    App::getDb()->onQueryEvent($ret, $builder, $start, $trace);
                }
                return $ret;
            } catch (\Exception $e) {
                $result = new Result();
                $result->setResult(false);
                $result->setLastError($e);
                return $result;
            }
        }, $this->getConnectionName());
    }

    function setConnectionName($connectionName): Connection
    {
        $this->_connectionName = $connectionName;
        return $this;
    }

    function setNonTraceable()
    {
        $this->_log = false;
        return $this;
    }

    function getConnectionName(): string
    {
        if (is_null($this->_connectionName)) {
            return DBConfig::getWriteDbName();
        }

        return $this->_connectionName;
    }
}