<?php
namespace wrxswoole\Core\Audit\Panel;

use wrxswoole\Core\Trace\Tracker;
use wrxswoole\Core\Trace\Point\DbQueryTrackerPoint;
use EasySwoole\Trace\Bean\TrackerPoint;
use wrxswoole\Core\Exception\Component\ExceptionHandler;

class DbPanel extends Panel
{

    const PanelName = 'audit/db';

    function getSqlType($sql)
    {
        return substr($sql, 0, strpos($sql, " "));
    }

    public function getTargetTrackerCategory()
    {
        return Tracker::TRACE_DBQUERY;
    }

    function record(TrackerPoint $pt, &$records)
    {
        $sql = $pt->getPointStartArgs()['sql'];
        $records[] = [
            $sql,
            Panel::LEVEL_PROFILE_BEGIN,
            "mysql",
            $pt->getPointStartTime(),
            ExceptionHandler::makeSimpleTraceByArray($pt->getPointStartArgs()['trace'])
        ];

        $records[] = [
            $sql,
            Panel::LEVEL_PROFILE_END,
            "mysql",
            $pt->getPointEndTime(),
            []
        ];
    }
}
?>