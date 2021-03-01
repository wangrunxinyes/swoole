<?php
namespace wrxswoole\Core\HttpController;

use EasySwoole\Annotation\Annotation;
use EasySwoole\Http\Request;
use wrxswoole\Core\Component\NonHttpRequest;
use wrxswoole\Core\Exception\BaseExceptionHandler;

class NonHttpEnvController extends CoreHttpController
{

    public $request = null;

    function __construct(?Annotation $annotation = null)
    {
        parent::__construct($annotation);
        $this->setStartAt(microtime(true))
            ->setNonRequestType()
            ->beforeRunAction();
    }

    function beforeStartCoroutine()
    {
        if (is_null($this->request)) {
            $this->request = new NonHttpRequest();
        }
        parent::beforeStartCoroutine();
        $this->getTracker()->enableAudit();
    }

    function onException(\Throwable $throwable): void
    {
        $this->success = false;
        $handler = new BaseExceptionHandler($throwable);
        $response = $handler->getResponse();
        if ($handler->isNotice()) {
            $this->log($response, $throwable->getMessage());
        }
        $this->getTracker()->finalize();
    }

    public function request(): Request
    {
        return $this->request;
    }
}
?>