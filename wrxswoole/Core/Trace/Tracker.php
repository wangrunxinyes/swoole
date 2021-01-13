<?php
namespace wrxswoole\Core\Trace;

use App\App;
use EasySwoole\Trace\Bean\TrackerPoint;
use wrxswoole\Core\BaseApp;
use wrxswoole\Core\Annotation\Tag\Authenticate;
use wrxswoole\Core\Audit\Audit;
use wrxswoole\Core\Audit\Panel\ErrorPanel;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Trace\Bean\TrackerBean;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class Tracker
{

    const INSTANCE = "TRACKER_INSTANCE";

    const TRACE_POINT = "TracePoint";

    const TRACE_LOG = "TraceLog";

    const TRACE_ERROR_POINT = "ErrorPoint";

    const TRACE_COROUTINE_THREAD = "CoreCoroutineThread";

    const TRACE_DBQUERY = 'DbQuery';

    /**
     *
     * @var \wrxswoole\Core\Trace\Bean\TrackerBean
     */
    public $bean = null;

    public $user = [];

    public $visitor = null;

    public $audit = null;

    public $user_id = null;

    public $finalized = false;

    public $data = [];

    public $errorPanel = null;

    /**
     *
     * @return Tracker
     */
    static function getInstance(): Tracker
    {
        return CoreCoroutineThread::getInstance()->getTracker();
    }

    function __construct($token)
    {
        $this->bean = new TrackerBean($token);
        $this->visitor = uniqid("visitor.");
    }

    public function setToken($token)
    {
        $this->bean->updateTrackerToken($token);
    }

    public function getToken()
    {
        return $this->bean->getTrackerToken();
    }

    public function setUser(array $info)
    {
        foreach ($info as $key => $val) {
            $this->bean->addAttribute($key, $val);
        }
    }

    public function setPoint($key, $data, $pointCategory = 'default')
    {
        $this->bean->setPoint($key, $data, $pointCategory);
    }

    public function endPoint(string $pointName, int $status = TrackerPoint::STATUS_SUCCESS, array $endArgs = [])
    {
        $this->bean->endPoint($pointName, $status, $endArgs);
    }

    public function addPoint(TrackerPoint $t)
    {
        $this->bean->addPoint($t);
    }

    public function finalize()
    {
        if (BaseApp::getInstance()->enableAudit() && ! is_null($this->audit)) {
            $this->audit->finalize();
            $this->export();
        }
    }

    function getFinalTrace()
    {
        return [
            'visitor' => $this->visitor,
            'tracker' => [
                "token" => $this->bean->getTrackerToken(),
                "attributes" => $this->bean->getAttributes(),
                "strack" => $this->finalizeStack()
            ]
        ];
    }

    function getErrorPanel(): ErrorPanel
    {
        if (is_null($this->errorPanel)) {
            $this->errorPanel = new ErrorPanel();
        }

        return $this->errorPanel;
    }

    public function finalizeStack()
    {
        $line = [];

        foreach ($this->bean->getPointStacks() as $tp) {
            /**
             *
             * @var \EasySwoole\Trace\Bean\TrackerPoint $tp
             */

            switch ($tp->getPointCategory()) {
                case Tracker::TRACE_POINT:
                    list ($start, $end) = $this->finalizeTrace($tp);
                    $line[$tp->getPointCategory()][$tp->getPointName()][$tp->getPointStartTime() . uniqid(".")] = $start;
                    if (! is_null($end)) {
                        $line[$tp->getPointCategory()][$tp->getPointName()][$tp->getPointEndTime() . uniqid(".end.")] = $end;
                    }
                    break;
                default:
                    $line[$tp->getPointCategory()][] = [
                        $tp->getPointName() => $this->finalizeTrace($tp)
                    ];
                    break;
            }
        }

        return $line;
    }

    function export()
    {
        $panels = App::getPanels();
        $records = [];
        foreach ($panels as $id => $panel) {
            $records[$id] = $panel->save();
        }

        $records = array_filter($records);

        if (! empty($records)) {
            // if ($module->batchSave)
            $this->audit->getEntry()->addBatchData($records, false);
            // else {
            // foreach ($records as $type => $record)
            // $entry->addData($type, $record, false);
            // }
        }
        // $this->messages = [];
    }

    function finalizeTrace(TrackerPoint $tp)
    {
        switch ($tp->getPointCategory()) {

            case Tracker::TRACE_ERROR_POINT:
                return [
                    'Time' => $tp->getPointStartTime(),
                    'Error' => $tp->getPointStartArgs()
                ];
                break;
            case Tracker::TRACE_LOG:
                return [
                    'Time' => $tp->getPointStartTime(),
                    'Info' => $tp->getPointStartArgs()
                ];
                break;
            case Tracker::TRACE_POINT:
                if (! is_numeric($tp->getPointEndTime())) {
                    return [
                        [
                            'Time' => $tp->getPointStartTime(),
                            'Info' => $tp->getPointStartArgs()
                        ],
                        null
                    ];
                } else {
                    return [
                        [
                            'Time' => $tp->getPointStartTime(),
                            'Info' => $tp->getPointStartArgs()
                        ],
                        [
                            'EndAt' => $tp->getPointEndTime(),
                            'Status' => $tp->getPointStatus(),
                            'EndArgs' => $tp->getPointEndArgs()
                        ]
                    ];
                }
            case Tracker::TRACE_COROUTINE_THREAD:
            default:
                return [
                    'Time' => [
                        'Start' => $tp->getPointStartTime(),
                        'End' => $tp->getPointEndTime()
                    ],
                    'Info' => [
                        "StartArgs" => $tp->getPointStartArgs(),
                        "EndArgs" => $tp->getPointEndArgs()
                    ],
                    'Status' => $tp->getPointStatus()
                ];
                break;
        }
    }

    function enableAudit()
    {
        if (BaseApp::getInstance()->enableAudit()) {
            $this->audit = new Audit();
        }
    }

    function getTraceId()
    {
        if (is_null($this->audit)) {
            return $this->visitor;
        }

        return $this->visitor . "." . $this->audit->getEntryId();
    }

    /**
     *
     * @return Tracker
     */
    static function init(): Tracker
    {
        $tracker = new Tracker(Authenticate::ANONYMOUS);

        $tracker->bean->addAttribute("uid", Authenticate::ANONYMOUS);

        return $tracker;
    }
}

?>