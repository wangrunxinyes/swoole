<?php
namespace wrxswoole\Core\Database\Model;

use App\App;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\Db\ClientInterface;
use EasySwoole\ORM\Db\Result;
use EasySwoole\ORM\Utility\TableObjectGeneration;
use EasySwoole\Trace\Bean\Tracker;
use wrxswoole\Core\Trace\Traits\TraceTrait;

/**
 *
 * @author wangrunxin
 *        
 */
class AdvancedTableObjectGeneration extends TableObjectGeneration
{

    private $traceable = true;
    use TraceTrait;

    /**
     *
     * @param bool $traceable
     * @return AdvancedTableObjectGeneration
     */
    function setTraceAble(bool $traceable): AdvancedTableObjectGeneration
    {
        $this->traceable = $traceable;
        return $this;
    }

    public function getTableColumnsInfo()
    {
        $query = new QueryBuilder();
        $query->raw("show full columns from {$this->tableName}");
        $traceable = $this->traceable;
        $data = $this->connection->getClientPool()->invoke(function (ClientInterface $client) use ($query, $traceable) {
            try {
                $builder = $query;
                $raw = true;
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
        }, 3);

        if ($this->connection->getConfig()->isFetchMode()) {
            $data->getResult()->setReturnAsArray(true);
            $this->tableColumns = [];
            while ($tem = $data->getResult()->fetch()) {
                $this->tableColumns[] = $tem;
            }
        } else {
            $this->tableColumns = $data->getResult();
        }

        if (! is_array($this->tableColumns)) {
            throw new \Exception("generationTable Error : " . $data->getLastError());
        }
        return $data->getResult();
    }
}

?>