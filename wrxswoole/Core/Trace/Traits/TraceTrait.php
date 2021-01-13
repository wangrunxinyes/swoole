<?php
namespace wrxswoole\Core\Trace\Traits;

use App\App;
use EasySwoole\Trace\Bean\TrackerPoint;
use wrxswoole\Core\Exception\Error\BaseException;
use wrxswoole\Core\Trace\Tracker;
use wrxswoole\Core\Log\Logger;
use wrxswoole\Core\Exception\Error\BreakPoint;

/**
 *
 * @author wangrunxin
 *        
 */
trait TraceTrait
{

    /**
     *
     * result;
     *
     * @var boolean
     */
    private $isSuccess = true;

    /**
     * add trace;
     */
    static function log($info, $category = "DEBUG", $level = Logger::LEVEL_INFO)
    {
        Tracker::getInstance()->setPoint(uniqid(microtime(true) . "."), [
            "info" => $info,
            "category" => $category,
            "level" => $level,
            "memory" => memory_get_usage(),
            "trace" => self::makeTrace()
        ], Tracker::TRACE_LOG);

        Logger::getInstance()->info($info, $category);
    }

    static function point($pointName, $info, $category = Tracker::TRACE_POINT)
    {
        Tracker::getInstance()->setPoint($pointName, [
            "info" => $info,
            "location" => self::makeTrace()
        ], $category);

        if (App::getInstance()->isDev()) {
            Logger::getInstance()->info([
                $pointName => $info,
                "location" => self::makeTrace()
            ], $category);
        }
    }

    static function endPoint(string $pointName, int $status = TrackerPoint::STATUS_SUCCESS, array $endArgs = [])
    {
        Tracker::getInstance()->endPoint($pointName, $status, [
            "info" => $endArgs,
            "location" => self::makeTrace()
        ]);

        if (App::getInstance()->isDev()) {
            Logger::getInstance()->info([
                $pointName => $endArgs,
                "status" => $status,
                "location" => self::makeTrace(),
                "type" => "end"
            ], Tracker::TRACE_POINT);
        }
    }

    static function debug($info = [])
    {
        $point = uniqid("debug.");
        $message = "debug only";

        $data = [
            "error" => is_null($message) ? App::DEFAULT_ERROR_MSG : $message,
            "info" => $info,
            "location" => self::makeTrace()
        ];
        Tracker::getInstance()->setPoint($point, $data, Tracker::TRACE_ERROR_POINT);

        throw new BaseException($data, is_null($message) ? (is_string($info) ? $info : App::DEFAULT_ERROR_MSG) : $message);
    }

    static function makeTrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 6);
        foreach ($trace as $key => $line) {
            $trace[$key] = self::fixLine($line);
        }
        return $trace;
    }

    static function fixLine($line)
    {
        if (! isset($line["file"])) {
            $line["file"] = "unknown";
            $line["line"] = 0;
        }

        return $line;
    }

    static function getTraceId()
    {
        return Tracker::getInstance()->getTraceId();
    }

    /**
     * exception;
     */
    static function error($info, $message = null, $throw = true)
    {
        $point = uniqid("error.");

        $data = [
            "error" => is_null($message) ? App::DEFAULT_ERROR_MSG : $message,
            "info" => $info,
            "location" => self::makeTrace()
        ];
        Tracker::getInstance()->setPoint($point, $data, Tracker::TRACE_ERROR_POINT);

        if ($throw) {
            throw new BaseException($data, is_null($message) ? (is_string($info) ? $info : App::DEFAULT_ERROR_MSG) : $message);
        }
    }

    static function breakRequest($info, $message = null)
    {
        $point = uniqid("break.");

        $data = [
            "info" => $info,
            "location" => self::makeTrace()
        ];
        Tracker::getInstance()->setPoint($point, $data, Tracker::TRACE_LOG);

        throw new BreakPoint($data, is_null($message) ? App::DEFAULT_BREAK_MSG : $message);
    }
}

?>