<?php
namespace wrxswoole\Core\Log;

use EasySwoole\EasySwoole\Logger as BaseLogger;
use wrxswoole\Core\Exception\Error\BaseException;
use EasySwoole\Component\Singleton;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class Logger
{

    /**
     * Error message level.
     * An error message is one that indicates the abnormal termination of the
     * application and may require developer's handling.
     */
    const LEVEL_ERROR = 0x01;

    /**
     * Warning message level.
     * A warning message is one that indicates some abnormal happens but
     * the application is able to continue to run. Developers should pay attention to this message.
     */
    const LEVEL_WARNING = 0x02;

    /**
     * Informational message level.
     * An informational message is one that includes certain information
     * for developers to review.
     */
    const LEVEL_INFO = 0x04;

    /**
     * Tracing message level.
     * An tracing message is one that reveals the code execution flow.
     */
    const LEVEL_TRACE = 0x08;

    /**
     * Profiling message level.
     * This indicates the message is for profiling purpose.
     */
    const LEVEL_PROFILE = 0x40;

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

    use Singleton;

    private $logger = null;

    function __construct()
    {
        $this->logger = BaseLogger::getInstance(new LogFormat());
    }

    public function info($msg, string $category = 'DEBUG')
    {
        $this->logger->info($this->formatMsg($msg), $category);
    }

    public function notice($msg, string $category = 'DEBUG')
    {
        $this->logger->notice($this->formatMsg($msg), $category);
    }

    public function waring($msg, string $category = 'DEBUG')
    {
        $this->logger->waring($this->formatMsg($msg), $category);
    }

    public function error($msg, string $category = 'DEBUG')
    {
        $this->logger->error($this->formatMsg($msg), $category);
    }

    public function formatMsg($msg)
    {
        if (is_string($msg)) {
            return $msg;
        }

        if (is_object($msg) || is_array($msg) || is_bool($msg)) {
            return print_r($msg, true);
        }

        throw new BaseException([
            "msg" => print_r($msg, true)
        ], "invalid log msg type.");
    }
}

?>