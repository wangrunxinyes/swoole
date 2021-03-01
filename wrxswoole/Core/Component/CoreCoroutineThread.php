<?php
namespace wrxswoole\Core\Component;

use App\App;
use EasySwoole\Component\Timer;
use EasySwoole\Component\WaitGroup;
use EasySwoole\EasySwoole\Logger;
use wrxswoole\Core\Exception\Error\BaseException;
use wrxswoole\Core\HttpController\CoreHttpController;
use wrxswoole\Core\HttpController\NonHttpEnvController;
use wrxswoole\Core\Model\Traits\CoroutineSafeSingleTon;
use wrxswoole\Core\Trace\Tracker;
use wrxswoole\Core\Trace\Traits\TraceTrait;
use wrxswoole\Core\Credential\Token;
use wrxswoole\Core\Exception\BaseExceptionHandler;

/**
 *
 * coroutine safe instance;
 * should init af the very fisrt in coroutine if you want to use CoreHttpControoler data or Tracker;
 *
 * @author WANG RUNXIN
 *        
 */
class CoreCoroutineThread
{

    const POINTCATE = 'CoreCoroutineThread';

    use CoroutineSafeSingleTon;

    use TraceTrait;

    private $tracker = null;

    private $main = false;

    private $components = [];

    /**
     *
     * @var array
     */
    private $attributes = [];

    /**
     *
     * @var CoreHttpController
     */
    private $controller = null;

    function __construct(CoreHttpController $controller = null, $attributes = [])
    {
        if (is_null($controller)) {
            print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            throw new \Exception("invalid use");
        }
        $this->controller = $controller;
        $this->attributes = $attributes;
    }

    /**
     *
     * @return CoreCoroutineThread
     */
    static function getInstance(): CoreCoroutineThread
    {
        return self::getStatic();
    }

    static function NonHttpEnv(callable $func, NonHttpRequest $request)
    {
        try {
            CoreCoroutineThread::NonHttpEnvStart(true, $request);
            $func();
        } catch (\Throwable $throwable) {
            if (CoreCoroutineThread::hasInstance()) {
                NonHttpEnvController::getInstance()->onException($throwable);
            } else {
                $response = (new BaseExceptionHandler($throwable))->getResponse();
                Logger::getInstance()->error(print_r($response, true), "NON_HTTP_ENV_EXCEPTION");
                throw new BaseException($response, App::DEFAULT_ERROR_MSG);
            }
        }
    }

    static function NonHttpEnvStart($main = false, NonHttpRequest $request = null)
    {
        $core = new NonHttpEnvController();
        $core->setReuest($request);
        return self::Start($core, null, $main);
    }

    static function HttpEnvStart(CoreHttpController $controller, $trace = null, $main = false)
    {
        return self::Start($controller, $trace, $main);
    }

    static function Start(CoreHttpController $controller, $trace = null, $main = false): CoreHttpController
    {
        $core = CoreCoroutineThread::getStatic($controller);
        if ($main) {
            $controller->beforeStartCoroutine();
        }
        $core->setMain($main);
        Tracker::getInstance()->setPoint(self::getCoroutineTraceId(), is_null($trace) ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2) : $trace, Tracker::TRACE_COROUTINE_THREAD);
        // App::getDb()->startTransaction();
        return $controller;
    }

    static function getCoreCid(): string
    {
        return CoreCoroutineThread::getInstance()->getCoreController()->getCid();
    }

    static function getCoroutineTraceId()
    {
        return "coroutine_" . self::getCoroutineId();
    }

    function setMain(bool $main)
    {
        $this->main = $main;
        if ($this->main) {
            $this->identifier = uniqid();
        }
    }

    static function Run(callable $func, CoreHttpController $core = null, WaitGroup $wait = null)
    {
        if (is_null($core)) {
            $core = CoreCoroutineThread::getInstance()->getCoreController();
        }

        if (! is_null($wait)) {
            $wait->add();
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        go(function () use ($func, &$core, $trace, $wait) {

            /**
             *
             * @var CoreHttpController $core
             */

            try {
                CoreCoroutineThread::Start($core, $trace);
                $func();
            } catch (\Throwable $throwable) {
                if (CoreCoroutineThread::hasInstance()) {
                    $core->onException($throwable);
                } else {
                    Logger::getInstance()->log(print_r($trace, true));
                    throw new \Exception("invalid use", null, $throwable);
                }
            }

            if (! is_null($wait)) {
                $wait->done();
            }
        });
    }

    function before_destroy()
    {
        $this->getTracker()->endPoint(self::getCoroutineTraceId());
        if ($this->main) {
            $this->getTracker()->finalize();
        }
    }

    /**
     *
     * @return CoreHttpController
     */
    public function getCoreController(): CoreHttpController
    {
        return $this->controller;
    }

    /**
     *
     * @return Tracker
     */
    public function getTracker(): Tracker
    {
        return $this->getCoreController()->getTracker();
    }

    /**
     *
     * @return Token
     */
    function getToken()
    {
        return $this->getCoreController()
            ->getTracker()
            ->getToken();
    }

    /**
     *
     * @param string $key
     * @param
     *            $value
     */
    public function set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     *
     * @param string $key
     */
    public function get(string $key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

    public function getRequestParam($key)
    {
        return $this->getCoreController()->getRequestParam($key);
    }

    static function Sleep($second = 5)
    {
        $wait = new WaitGroup();
        $wait->add();
        Timer::getInstance()->after($second * 1000, function () use ($wait) {
            $wait->done();
        });

        $wait->wait($second + 1);
    }
}

?>