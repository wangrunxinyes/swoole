<?php
namespace wrxswoole\Core\Database;

use App\App;
use App\Db\DBConfig;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager as base;
use EasySwoole\ORM\Db\ClientInterface;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\ConnectionInterface;
use EasySwoole\ORM\Db\Result;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Pool\Exception\PoolEmpty;
use wrxswoole\Core\Database\Component\Schema;
use wrxswoole\Core\Trace\Traits\TraceTrait;

/**
 * coroutine un-safe dbmanager;
 * commit/rollback for all txn;
 *
 * @author WANG RUNXIN
 *        
 */
class DbManager extends base
{
    use TraceTrait;

    function createCommand($sql = null, $params = [], $connectionName = null)
    {
        \wrxswoole\Core\Database\Connection::create($connectionName)->createCommand($sql, $params);
    }

    function onQueryEvent($ret, $temp, $start, $trace = [])
    {
        if ($this->onQuery) {
            $temp = clone $temp;
            call_user_func($this->onQuery, $ret, $temp, $start, null, $trace);
        }
    }

    function addConnection(ConnectionInterface $connection, string $connectionName = 'default'): base
    {
        if (isset($this->connections[$connectionName])) {
            $this->destoryConnection($this->connections[$connectionName]);
        }

        return parent::addConnection($connection, $connectionName);
    }

    function destoryConnection(ConnectionInterface &$connection)
    {
        $pool = $connection->getClientPool();
        if (! is_null($pool)) {
            $pool->destroy();
        }

        unset($connection);
    }

    /**
     *
     * @param QueryBuilder $builder
     * @param bool $raw
     * @param string|\EasySwoole\ORM\Db\ClientInterface $connection
     * @param float|null $timeout
     * @return Result
     * @throws Exception
     * @throws \Throwable
     */
    function query(QueryBuilder $builder, bool $raw = false, $connection = 'default', float $timeout = null, bool $traceable = true): Result
    {
        if (is_null($timeout)) {
            $timeout = 2;
        }

        return App::getDb()->invoke(function (ClientInterface $client) use ($builder, $raw, $traceable) {
            try {
                $start = microtime(true);
                $ret = $client->query($builder, $raw);
                if ($traceable !== false) {
                    App::getDb()->onQueryEvent($ret, $builder, $start, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5));
                }
                return $ret;
            } catch (\Exception $e) {
                $result = new Result();
                $result->setLastError($e);
                return $result;
            }
        }, $connection, $timeout);
    }
}
?>