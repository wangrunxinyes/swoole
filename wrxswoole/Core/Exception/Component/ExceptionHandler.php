<?php

namespace wrxswoole\Core\Exception\Component;

use App\App;
use EasySwoole\EasySwoole\SysConst;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Exception\Error\BaseException;
use wrxswoole\Core\Exception\Error\MutipleException;
use wrxswoole\Core\Exception\Error\Notice;
use wrxswoole\Core\HttpResponse\HttpResponseResult;
use wrxswoole\Core\Log\Logger;
use wrxswoole\Core\Trace\Tracker;
use wrxswoole\Core\Trace\Traits\TraceTrait;
use wrxswoole\Core\Exception\Error\BreakPoint;
use EasySwoole\Http\Response;
use wrxswoole\Core\Component\CoreDi;
use wrxswoole\Core\Exception\BaseExceptionHandler;
use wrxswoole\Core\Exception\BaseExceptionHanlder;
use wrxswoole\Core\Exception\Error\PageNotFoundException;

/**
 *
 * @author WANG RUNXIN
 *        
 */
abstract class ExceptionHandler
{
    use TraceTrait;

    /**
     *
     * @var \Throwable|Null
     */
    public $throwable = null;

    /**
     *
     * @var Response|NULL
     */
    protected $response = null;

    protected $responseCode = null;

    protected $responseStatus = null;

    function __construct(\Throwable $throwable, Response $response = null)
    {
        $this->throwable = $throwable;
        $this->response = $response;
    }

    /**
     * getInstance
     *
     * @param  mixed $throwable
     * @param  mixed $response
     * @return self
     */
    static function getInstance(\Throwable $throwable, Response $response = null): self
    {
        $className = CoreDi::getInstance()->get(SysConst::ERROR_HANDLER);
        if(is_null($className)){
            $className = BaseExceptionHandler::class;
        }

        return new $className($throwable, $response);
    }

    public function end()
    {
        $response = $this->getResponse();

        if (is_null($this->response)) {
            CoreCoroutineThread::getInstance()->getCoreController()->writeJson($this->getResponseCode(), $response, $this->getResponseStatus());
            CoreCoroutineThread::getInstance()->getCoreController()
                ->response()
                ->end();
        } else {
            $this->response->write(json_encode([
                "code" => $this->getResponseCode(),
                "status" => $this->getResponseStatus(),
                "result" => $response
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response->withStatus($this->getResponseCode());
            $this->response->end();
        }
    }

    private function isPageNotFound(): bool
    {
        if ($this->throwable instanceof PageNotFoundException) {
            return true;
        }

        return false;
    }

    public function isNotice(): bool
    {
        if ($this->throwable instanceof Notice) {
            return true;
        }

        if ($this->throwable instanceof MutipleException) {
            return $this->throwable->isNotice();
        }

        return false;
    }

    public function getResponseStatus()
    {
        if (!is_null($this->responseStatus)) {
            return $this->responseStatus;
        }

        if ($this->isNotice()) {
            return HttpResponseResult::STATUS_FAILED;
        }

        return HttpResponseResult::STATUS_ERROR;
    }

    public function getResponseCode()
    {
        if (!is_null($this->responseCode)) {
            return $this->responseCode;
        }

        if ($this->isNotice()) {
            $this->responseCode = HttpResponseResult::CODE_OK;
        } elseif ($this->isPageNotFound()) {
            $this->responseCode = HttpResponseResult::CODE_NOT_FOUND;
        } else {
            $this->responseCode = HttpResponseResult::CODE_ERROR;
        }

        return $this->responseCode;
    }

    public function getResponse(): array
    {
        try {
            if ($this->isNotice()) {
                // skip error panel log on notice type exception;
                return $this->makeNoticeResponse();
            }

            // get debug msg;
            $debug = $this->makeDebugResponse();

            // log debug data;
            if (is_null($this->response)) {
                $this->log($debug, "Error", Logger::LEVEL_ERROR);
                Tracker::getInstance()->getErrorPanel()->log($this->throwable);
            }

            if (App::getInstance()->isDev()) {
                return $debug;
            }

            return $this->makeProduceResponse();
        } catch (\Throwable $e) {

            $debug = [
                "trace_id" => Tracker::getInstance()->getTraceId(),
                "runtime exception" => self::formatExceptionTrace($this->throwable),
                "recored_exception" => [
                    "message" => "failed to recored error data",
                    "debug" => self::formatExceptionTrace($e)
                ]
            ];

            Logger::getInstance()->error($debug);

            $this->responseCode = HttpResponseResult::CODE_ERROR;
            $this->responseStatus = HttpResponseResult::STATUS_ERROR;

            return $this->makeProduceResponse();
        }
    }

    abstract function makeProduceResponse(): array;

    public function makeNoticeResponse(): array
    {
        return [
            "msg" => $this->throwable->getMessage(),
            "hint" => $this->throwable->hint,
            "visitor" => Tracker::getInstance()->getTraceId()
        ];
    }

    public function makeDebugResponse(): array
    {
        return [
            'error' => self::formatExceptionTrace($this->throwable),
            'debug' => [
                'core' => $this->makeCoreTrace(),
                'trace' => $this->makeTraceData()
            ]
        ];
    }

    private function makeCoreTrace()
    {
        if (!is_null($this->response)) {
            return [];
        }

        return Tracker::getInstance()->getFinalTrace();
    }

    private function makeTraceData(): array
    {
        return ExceptionHandler::makeSimpleTrace($this->throwable);
    }
}
