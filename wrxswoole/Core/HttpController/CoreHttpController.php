<?php
namespace wrxswoole\Core\HttpController;

use App\App;
use EasySwoole\Annotation\Annotation;
use EasySwoole\Component\Di as IOC;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\HttpAnnotation\AnnotationTag\CircuitBreaker;
use EasySwoole\HttpAnnotation\AnnotationTag\Context;
use EasySwoole\HttpAnnotation\AnnotationTag\DocTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\DocTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\DocTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\DocTag\ApiSuccess;
use EasySwoole\Http\AbstractInterface\Controller;
use Swoole\Coroutine;
use wrxswoole\Core\Annotation\CoreAnnotation;
use wrxswoole\Core\Annotation\Tag\Authenticate;
use wrxswoole\Core\Annotation\Tag\DI;
use wrxswoole\Core\Annotation\Tag\Method;
use wrxswoole\Core\Annotation\Tag\Param;
use wrxswoole\Core\Annotation\Tag\Route;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Credential\Token;
use wrxswoole\Core\Exception\ExceptionHandler;
use wrxswoole\Core\Exception\Error\MutipleException;
use wrxswoole\Core\HttpResponse\HttpResponseResult;
use wrxswoole\Core\Trace\Tracker;
use wrxswoole\Core\Trace\Traits\TraceTrait;
use wrxswoole\Core\Validator\Interfaces\ValidateInterface;

/**
 *
 * editable base controller
 *
 * @author WANG RUNXIN
 */
abstract class CoreHttpController extends Controller
{

    use TraceTrait;

    private $actionAnnotations = [];

    private $methodAnnotations = [];

    private $propertyAnnotations = [];

    private $actionName = null;

    /**
     *
     * @var \EasySwoole\Annotation\Annotation
     */
    private $annotation;

    private $key = "init";

    private $tracker = null;

    private $actionArgs = [];

    private $success = true;

    private $cid = null;

    private $startAt = null;

    private $httpRequest = true;

    private $responseCode = HttpResponseResult::CODE_OK;

    public $request;

    public $response;

    public $params = [];

    public function __construct(?Annotation $annotation = null)
    {
        parent::__construct();

        if ($annotation == null) {
            $this->annotation = new CoreAnnotation();
        } else {
            $this->annotation = $annotation;
        }

        $this->addAnnotationTags();
    }

    private function getLowerCaseMethodReflections()
    {
        $result = [];
        foreach ($this->getAllowMethodReflections() as $name => $obj) {
            $result[strtolower($name)] = $obj;
        }

        return $result;
    }

    static function getInstance(): CoreHttpController
    {
        return CoreCoroutineThread::getInstance()->getCoreController();
    }

    /**
     *
     * @Authenticate(false)
     */
    function index()
    {
        $this->writeSuccessJson([
            "message" => "default action"
        ]);
    }

    function setActionName($name)
    {
        $this->actionName = $name;
    }

    /**
     *
     * @Authenticate(false)
     */
    protected function actionNotFound(?string $action)
    {
        $this->setResponseCode(HttpResponseResult::CODE_NOT_FOUND);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (! is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }
        $this->beforeResponseEnd();
        $this->response()->write(file_get_contents($file));
    }

    /**
     *
     * @return \wrxswoole\Core\Trace\Tracker
     */
    public function getTracker()
    {
        return $this->tracker;
    }

    function isHttpRequest(): bool
    {
        return $this->httpRequest;
    }

    /**
     *
     * @return CoreHttpController
     */
    function setNonRequestType(): CoreHttpController
    {
        $this->httpRequest = false;
        return $this;
    }

    function setReuest($request)
    {
        $this->request = $request;
    }

    public function addAnnotationTags()
    {
        $this->annotation->addParserTag(new Method());
        $this->annotation->addParserTag(new Param());
        $this->annotation->addParserTag(new Context());
        $this->annotation->addParserTag(new Di());
        $this->annotation->addParserTag(new CircuitBreaker());
        $this->annotation->addParserTag(new Api());
        $this->annotation->addParserTag(new ApiFail());
        $this->annotation->addParserTag(new ApiSuccess());
        $this->annotation->addParserTag(new ApiRequestExample());
        $this->annotation->addParserTag(new Authenticate());
    }

    public function beforeRunAction()
    {
        $this->success = true;

        foreach ($this->getAllowMethodReflections() as $name => $reflection) {
            $ret = $this->annotation->getAnnotation($reflection);
            if (! empty($ret)) {
                $this->methodAnnotations[$name] = $ret;
            }
        }
        foreach ($this->getPropertyReflections() as $name => $reflection) {
            $ret = $this->annotation->getAnnotation($reflection);
            if (! empty($ret)) {
                $this->propertyAnnotations[$name] = $ret;
            }
        }
    }

    /**
     *
     * @param string $time
     * @return CoreHttpController
     */
    public function setStartAt($time): CoreHttpController
    {
        $this->startAt = $time;
        return $this;
    }

    public function getStartAt()
    {
        return $this->startAt;
    }

    public function run(?string $actionName, Request $request, Response $response, callable $actionHook = null)
    {
        $this->params = $request->getRequestParam();
        $this->startAt = microtime(true);
        $this->actionName = $actionName = $this->getRoute($actionName);
        Tracker::getInstance()->enableAudit();

        set_time_limit(0);

        $this->beforeRunAction();

        foreach ($this->propertyAnnotations as $name => $propertyAnnotation) {
            if (! empty($propertyAnnotation['Context'])) {
                $context = $propertyAnnotation['Context'][0]->key;
                if (! empty($context)) {
                    $this->{$name} = ContextManager::getInstance()->get($context);
                }
            }

            if (! empty($propertyAnnotation['DI'])) {
                $key = $propertyAnnotation['DI'][0]->key;
                if (! empty($key)) {
                    $this->{$name} = IOC::getInstance()->get($key);
                }
            }
        }

        if (App::getInstance()->isDev()) {
            $this->log([
                "Debug" => [
                    "ClassName" => get_class($this),
                    "Function" => $actionName,
                    "swoole_received_cookies" => $request->getCookieParams(),
                    "swoole_received_headers" => $request->getHeaders()
                ]
            ], "Meta");
        }

        return parent::__hook($actionName, $request, $response);
    }

    protected function reflection()
    {
        $actionName = $this->actionName;
        if (isset($this->methodAnnotations[$actionName])) {
            $annotations = $this->methodAnnotations[$actionName];
            $wait = new \EasySwoole\Component\WaitGroup();
            $errors = [];
            foreach ($annotations as $list) {
                foreach ($list as $annotation) {
                    if ($annotation instanceof ValidateInterface)
                        if ($annotation instanceof Authenticate) {
                            try {
                                $annotation->validate();
                            } catch (\Exception $e) {
                                $errors[] = $e;
                            }
                        } else {
                            CoreCoroutineThread::Run(function () use ($annotation, &$errors) {
                                try {
                                    $annotation->validate();
                                } catch (\Exception $e) {
                                    $errors[] = $e;
                                }
                            }, $this, $wait);
                        }
                }
            }

            $wait->wait(0);

            if (count($errors) != 0) {
                throw new MutipleException($errors);
            }
        }

        return true;
    }

    // 允许用户修改整个请求执行流程
    protected function __exec()
    {
        $actionName = $this->actionName;
        $forwardPath = null;
        try {
            if ($this->onRequest($actionName) !== false) {
                if (isset($this->getLowerCaseMethodReflections()[$actionName])) {
                    $this->reflection();
                    $forwardPath = $this->$actionName();
                } else {
                    $forwardPath = $this->actionNotFound($actionName);
                }
            }
        } catch (\Throwable $throwable) {
            // 若没有重构onException，直接抛出给上层
            $this->onException($throwable);
        } finally {
            try {
                $this->afterAction($actionName);
            } catch (\Throwable $throwable) {
                $this->onException($throwable);
            } finally {
                try {
                    $this->gc();
                } catch (\Throwable $throwable) {
                    $this->onException($throwable);
                }
            }
        }
        return $forwardPath;
    }

    function beforeStartCoroutine()
    {
        $this->tracker = Tracker::init();
        $this->cid = Coroutine::getCid();
    }

    function getCid()
    {
        return $this->cid;
    }

    function getDbManager($main_cid): array
    {
        return $this->dbManagerCid;
    }

    function __hook(?string $actionName, Request $request, Response $response, callable $actionHook = null)
    {
        try {
            $this->request = $request;
            $this->response = $response;

            $core = clone $this;
            CoreCoroutineThread::Start($core, $this->tracker, true);
            $data = $core->run($actionName, $request, $response, $actionHook);

            if (is_null($data)) {
                // TODO: log error and response msg;
            }

            $this->beforeResponseEnd();
            unset($core);
        } catch (\Throwable $throwable) {
            if (CoreCoroutineThread::hasInstance()) {
                return $this->onException($throwable);
            } else {
                throw new \Exception("invalid use", null, $throwable);
            }
        }

        return $data;
    }

    function setResponseCode($code)
    {
        $this->responseCode = $code;
    }

    function getResponseCode()
    {
        return $this->responseCode;
    }

    public function beforeResponseEnd()
    {
        $this->response()->withStatus($this->responseCode);
    }

    /**
     * do something before run action.
     * call $this->response()->end() if you need to end this request
     *
     * @param string $action
     * @return bool
     */
    public function beforeRequest(?string $action): ?bool
    {
        Logger::getInstance()->log($action);

        if ($this->response()->isEndResponse()) {
            return false;
        }

        return true;
    }

    protected function onRequest(?string $action): ?bool
    {
        if (! $this->beforeRequest($action)) {
            return false;
        }
        return true;
    }

    public function onException(\Throwable $throwable): void
    {
        $this->success = false;
        (new ExceptionHandler($throwable))->end();
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function response(): Response
    {
        if (is_null($this->response)) {
            $this->response = new Response();
        }

        return $this->response;
    }

    public function writeFailedJson($data)
    {
        if (! is_array($data)) {
            $data = [
                "data" => print_r($data, true)
            ];
        }

        $this->writeJson(HttpResponseResult::CODE_OK, $data, HttpResponseResult::STATUS_FAILED);

        $this->response()->end();
    }

    /**
     *
     * @param string|array $data
     * @param boolean $end
     */
    public function writeSuccessJson($data, $end = true)
    {
        if (! is_array($data)) {
            $data = [
                "data" => print_r($data, true)
            ];
        }

        $this->writeJson(HttpResponseResult::CODE_OK, $data, HttpResponseResult::STATUS_OK);
        if ($end) {
            $this->response()->end();
        }
    }

    protected function getMethodAnnotations(): array
    {
        return $this->methodAnnotations;
    }

    protected function getAnnotation(): Annotation
    {
        return $this->annotation;
    }

    function getActionName(): ?string
    {
        return $this->actionName;
    }

    public function writeJson($statusCode = 200, $result = null, $status = null, $msg = null)
    {
        $data = [
            "code" => $statusCode,
            "status" => $status,
            "result" => $result
        ];

        if (! is_null($msg)) {
            $data["msg"] = $msg;
        }

        return $this->writeUnformatJson($data, $statusCode);
    }

    public function addActionArg($key, $value)
    {
        $this->actionArgs[$key] = $value;
    }

    public function getRequestParam($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }

        return null;
    }

    public function writeUnformatJson(array $data, $statusCode = 200)
    {
        if (! $this->response()->isEndResponse()) {
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        } else {
            return false;
        }
    }

    protected function getRoute(?string $actionName): string
    {
        $method = $this->getAllowMethodReflections();

        if (isset($method[$actionName])) {
            return $actionName;
        }

        $annotations = $this->getMethodAnnotations();

        foreach ($annotations as $action => $reflections) {
            if (! isset($reflections[Route::TAG])) {
                continue;
            }

            foreach ($reflections[Route::TAG] as $route) {
                /**
                 *
                 * @var Route $route
                 */
                if (in_array($actionName, $route->allow)) {
                    return $action;
                }
            }
        }

        return $actionName;
    }
}

?>