<?php
namespace wrxswoole\Core;

use App\App;
use App\Db\DBConfig;
use App\Task\Tasks;
use EasySwoole\Component\Di;
use EasySwoole\Component\Singleton;
use EasySwoole\Config\AbstractConfig;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\SysConst;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\GlobalParamHook;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Session\Session;
use EasySwoole\Session\SessionFileHandler;
use Swoole\Coroutine\Scheduler;
use wrxswoole\Core\Audit\Panel\DbPanel;
use wrxswoole\Core\Audit\Panel\LogPanel;
use wrxswoole\Core\Audit\Panel\RequestPanel;
use wrxswoole\Core\Database\DbManager;
use wrxswoole\Core\Http\Module;
use wrxswoole\Core\Log\ConsoleLogger;
use wrxswoole\Core\Task\TaskManager;
use wrxswoole\Core\Trace\Point\DbQueryTrackerPoint;

/**
 *
 * @author WANG RUNXIN
 *        
 */
abstract class BaseApp extends AbstractConfig
{

    use Singleton;

    const DEFAULT_ERROR_MSG = "an internal error has occurred";

    const DEFAULT_BREAK_MSG = "an internal failure has occurred";

    const ENABLE_AUDIT = "ENABLE_AUDIT";

    public $compressData = true;

    protected $modules = [];

    static function mainServerCreate(EventRegister $register)
    {
        // QueueService::register();
        TaskManager::register($register, Tasks::All()); // 可以自己实现一个标准的session handler
        $handler = new SessionFileHandler(EASYSWOOLE_TEMP_DIR);
        GlobalParamHook::getInstance()->hookDefault();
        Session::getInstance($handler, 'easy_session', 'session_dir');
    }

    static function initialize()
    {
        $app = App::getInstance();

        date_default_timezone_set('Asia/Shanghai');

        ConsoleLogger::getInstance()->info("welcome to wswoole", [
            "timezone" => 'Asia/Shanghai'
        ]);

        /**
         * init db connection;
         */
        DBConfig::initialize();

        /**
         * create audit if need;
         */
        $app->createAudit();
        $app->setModules();

        Di::getInstance()->set(SysConst::HTTP_CONTROLLER_MAX_DEPTH, 10);
        Di::getInstance()->set(SysConst::HTTP_DISPATCHER_NAMESPACE, 'wrxswoole\\Core\\Http\\');
        Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER, "wrxswoole\Core\Exception\HttpExceptionHandler::Handle");

        /**
         * 清理调度器内可能注册的定时器
         * 不要影响到swoole server 的event loop
         */
        \Swoole\Timer::clearAll();
    }

    static function onRequest(Request $request, Response $response)
    {
        GlobalParamHook::getInstance()->onRequest($request, $response);
    }

    function createAudit()
    {
        /**
         * skip audit connection;
         */
        if (! $this->enableAudit()) {
            ConsoleLogger::getInstance()->debug("audit system", [
                "skip"
            ]);
            return;
        }

        ConsoleLogger::getInstance()->debug("audit system", [
            "start to record"
        ]);
        /**
         * callback on db query;
         */
        self::getDb()->onQuery(function ($ret, $temp, $start, $client = null, $trace = []) {
            new DbQueryTrackerPoint($ret, $temp, $start, $client, $trace);
        });
    }

    function setModules()
    {
        $this->initModules();

        $format = [];
        /**
         *
         * @var $module Module
         */
        foreach ($this->modules as $module) {
            $format[$module->getName()] = $module;
        }

        $this->modules = $format;
    }

    function getModules()
    {}

    function initModules()
    {}

    function getModule($id)
    {
        $id = strtolower($id);
        if (! isset($this->modules[$id])) {
            return null;
        }

        return $this->modules[$id];
    }

    function resetConnectionPool()
    {
        $scheduler = new Scheduler();
        $scheduler->add(function () {
            DBConfig::resetConnectionPool();
        });
        // 执行调度器内注册的全部回调
        $scheduler->start();
    }

    function enableAudit(): bool
    {
        return Config::getInstance()->getConf(BaseApp::ENABLE_AUDIT);
    }

    public function isDev()
    {
        return Config::getInstance()->getConf("DEV");
    }

    public function getConf($key = null)
    {
        return Config::getInstance()->getConf($key);
    }

    public function load(array $array): bool
    {
        return Config::getInstance()->load($array);
    }

    public function merge(array $array): bool
    {
        return Config::getInstance()->merge($array);
    }

    public function clear(): bool
    {
        return Config::getInstance()->clear();
    }

    public function setConf($key, $val): bool
    {
        return Config::getInstance()->setConf($key, $val);
    }

    public static function getDb(): DbManager
    {
        return DbManager::getInstance();
    }

    static function getPanels()
    {
        return [
            DbPanel::PanelName => new DbPanel(),
            LogPanel::PanelName => new LogPanel(),
            RequestPanel::PanelName => new RequestPanel()
        ];
    }
}

?>