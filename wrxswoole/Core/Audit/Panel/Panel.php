<?php
namespace wrxswoole\Core\Audit\Panel;

use EasySwoole\Trace\Bean\TrackerPoint;
use wrxswoole\Core\Trace\Tracker;

abstract class Panel
{

    /**
     * Profiling message level.
     * This indicates the message is for profiling purpose. It marks the
     * beginning of a profiling block.
     */
    const LEVEL_PROFILE_BEGIN = 0x50;

    /**
     * Profiling message level.
     * This indicates the message is for profiling purpose. It marks the
     * end of a profiling block.
     */
    const LEVEL_PROFILE_END = 0x60;

    /**
     *
     * {@inheritdoc}
     */
    function save()
    {
        return [
            'messages' => $this->getProfileLogs()
        ];
    }

    /**
     *
     * @param TrackerPoint $pt
     * @return bool
     */
    function isTrackerPoint(TrackerPoint $pt): bool
    {
        $target = $this->getTargetTrackerCategory();

        if (is_string($target)) {
            return $pt->getPointCategory() == $target;
        }
        if (is_array($target)) {
            return in_array($pt->getPointCategory(), $target);
        }

        return false;
    }

    function getProfileLogs()
    {
        $points = Tracker::getInstance()->bean->getPointStacks();

        $records = [];

        foreach ($points as $pt) {
            if (! $this->isTrackerPoint($pt)) {
                continue;
            }

            $this->record($pt, $records);
        }

        return $records;
    }

    abstract function record(TrackerPoint $pt, &$records);

    abstract function getTargetTrackerCategory();
}

?>