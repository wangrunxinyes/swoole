<?php
namespace wrxswoole\Core\Trace\Point;

use wrxswoole\Core\Component\CoreCoroutineThread;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\Db\Result;
use EasySwoole\Trace\Bean\TrackerPoint;
use wrxswoole\Core\Trace\Tracker;

class DbQueryTrackerPoint
{

    public $start = null;

    /**
     *
     * @var \EasySwoole\ORM\Db\Result
     */
    public $ret = null;

    /**
     *
     * @var \EasySwoole\Mysqli\QueryBuilder
     */
    public $builder = null;

    /**
     *
     * @var FlexTrackerPoint
     */
    public $t = null;

    public $trace = [];

    function __construct(Result $ret, QueryBuilder $temp, $start, $client = null, $trace = [])
    {
        $this->start = $start;
        $this->ret = $ret;
        $this->builder = $temp;
        $this->trace = $trace;
        $this->createStartPoint();
        $this->createEndPoint();
        CoreCoroutineThread::getInstance()->getTracker()->addPoint($this->t);
    }

    public function createStartPoint()
    {
        $sql = $this->builder->getLastQuery();
        $pointName = md5($sql);
        $this->t = new FlexTrackerPoint($pointName, [
            "sql" => $sql,
            "trace" => $this->trace
        ], Tracker::TRACE_DBQUERY);
        $this->t->setStart($this->start);
    }

    public function createEndPoint()
    {
        $this->t->endPoint(TrackerPoint::STATUS_SUCCESS, [
            'affectedRows' => $this->ret->getAffectedRows()
        ]);
    }
}

?>