<?php
namespace wrxswoole\Core\Log;

use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\SysConst;
use EasySwoole\Log\LoggerInterface;
use wrxswoole\Core\Trace\Tracker;

/**
 * log formatter class
 *
 * @author WANG RUNXIN
 *        
 */
class LogFormat implements LoggerInterface
{

    private $logDir;

    function __construct(string $logDir = null)
    {
        if (empty($logDir)) {
            $logDir = getcwd();
        }
        $this->logDir = $logDir;
        defined('EASYSWOOLE_LOG_DIR') or define('EASYSWOOLE_LOG_DIR', $logDir);
        Di::getInstance()->set(SysConst::LOGGER_HANDLER, $this);
    }

    function log(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'DEBUG'): string
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $filePath = EASYSWOOLE_LOG_DIR . "/" . $levelStr . "/";
        $fileName = date('Y-m-d') . ".log";

        if (! file_exists($filePath)) {
            mkdir($filePath);
        }

        $str = "[{$date}][{$category}][{$levelStr}] : [{$msg}]\n";
        file_put_contents($filePath . $fileName, "{$str}", FILE_APPEND | LOCK_EX);
        return $str;
    }

    function console(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'DEBUG')
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $tracker_id = Tracker::getInstance()->visitor;
        $temp = $this->colorString("[{$date}][{$tracker_id}][{$category}][{$levelStr}] : [{$msg}]", $logLevel) . "\n";
        fwrite(STDOUT, $temp);
    }

    private function colorString(string $str, int $logLevel)
    {
        switch ($logLevel) {
            case self::LOG_LEVEL_INFO:
                $out = "[42m";
                break;
            case self::LOG_LEVEL_NOTICE:
                $out = "[43m";
                break;
            case self::LOG_LEVEL_WARNING:
                $out = "[45m";
                break;
            case self::LOG_LEVEL_ERROR:
                $out = "[41m";
                break;
            default:
                $out = "[42m";
                break;
        }
        return chr(27) . "$out" . "{$str}" . chr(27) . "[0m";
    }

    private function levelMap(int $level)
    {
        switch ($level) {
            case self::LOG_LEVEL_INFO:
                return 'INFO';
            case self::LOG_LEVEL_NOTICE:
                return 'NOTICE';
            case self::LOG_LEVEL_WARNING:
                return 'WARNING';
            case self::LOG_LEVEL_ERROR:
                return 'ERROR';
            default:
                return 'UNKNOWN';
        }
    }
}