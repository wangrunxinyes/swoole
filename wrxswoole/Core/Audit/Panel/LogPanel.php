<?php
namespace wrxswoole\Core\Audit\Panel;

use EasySwoole\Trace\Bean\TrackerPoint;
use wrxswoole\Core\Trace\Tracker;
use wrxswoole\Core\Log\Logger;

class LogPanel extends Panel
{

    const PanelName = 'audit/log';

    public function getTargetTrackerCategory()
    {
        return [
            Tracker::TRACE_LOG,
            Tracker::TRACE_POINT
        ];
    }

    function record(TrackerPoint $pt, &$records)
    {
        switch ($pt->getPointCategory()) {
            case Tracker::TRACE_LOG:
                return $this->normal($pt, $records);
            case Tracker::TRACE_POINT:
                return $this->point($pt, $records);
        }
    }

    function normal(TrackerPoint $pt, &$records)
    {
        $msg = $pt->getPointStartArgs();
        $records[] = [
            $this->filter($msg, "info"),
            $this->filter($msg, "level"),
            $this->filter($msg, "category"),
            $pt->getPointStartTime(),
            $this->filter($msg, "trace"),
            $this->filter($msg, "memory")
        ];
    }

    function point(TrackerPoint $pt, &$records)
    {
        $msg = $pt->getPointStartArgs();
        $records[] = [
            $this->filter($msg, "info"),
            Logger::LEVEL_INFO,
            $pt->getPointName(),
            $pt->getPointStartTime(),
            $this->filter($msg, "location"),
            null
        ];

        if (is_numeric($pt->getPointEndTime())) {
            $msg = $pt->getPointEndArgs();
            $records[] = [
                $this->filter($msg, "info"),
                Logger::LEVEL_INFO,
                $pt->getPointName(),
                $pt->getPointEndTime(),
                $this->filter($msg, "location"),
                null
            ];
        }
    }

    function filter($data, $key)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }

        return null;
    }
}
?>