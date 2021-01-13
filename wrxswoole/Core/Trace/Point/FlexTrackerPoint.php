<?php
namespace wrxswoole\Core\Trace\Point;

use EasySwoole\Trace\Bean\TrackerPoint;

class FlexTrackerPoint extends TrackerPoint
{

    protected $pointStartTime;

    protected $pointEndTime;

    public function setStart($start)
    {
        $this->pointStartTime = $start;
    }

    /**
     *
     * @return mixed
     */
    public function getPointStartTime()
    {
        if (! is_null($this->pointStartTime)) {
            return $this->pointStartTime;
        }
        return parent::getPointStartTime();
    }
}

?>