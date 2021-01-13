<?php
namespace wrxswoole\Core\Audit\Panel;

use EasySwoole\Trace\Bean\TrackerPoint;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\HttpController\CoreHttpController;
use wrxswoole\Core\Audit\Model\AuditEntry;

/**
 * RequestPanel
 *
 * @package bedezign\yii2\audit\panels
 */
class RequestPanel extends Panel
{

    const PanelName = 'audit/request';

    /**
     *
     * @var array
     */
    public $ignoreKeys = [];

    /**
     *
     * {@inheritdoc}
     */
    /**
     *
     * @inheritdoc
     */
    public function save()
    {
        return $this->cleanData($this->loadRequest());
    }

    public function isAjax()
    {
        return false;
    }

    public function getIsPjax()
    {
        return false;
    }

    public function getRequestBody()
    {
        return CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->getParsedBody();
    }

    protected function loadRequest()
    {
        $controller = CoreCoroutineThread::getInstance()->getCoreController();
        $headers = $controller->request()->getHeaders();
        $requestHeaders = [];
        foreach ($headers as $name => $value) {
            if (is_array($value) && count($value) == 1) {
                $requestHeaders[$name] = current($value);
            } else {
                $requestHeaders[$name] = $value;
            }
        }

        $responseHeaders = [];
        $headers = $controller->response()->getHeaders();
        foreach ($headers as $name => $value) {
            if (is_array($value) && count($value) == 1) {
                $responseHeaders[$name] = current($value);
            } else {
                $responseHeaders[$name] = $value;
            }
        }

        $data = [
            'flashes' => $this->getFlashes(),
            'statusCode' => $controller->response()->getStatusCode(),
            'requestHeaders' => $requestHeaders,
            'responseHeaders' => $responseHeaders,
            'route' => $this->getRoute($controller),
            'GET' => $controller->request()->getQueryParams(),
            'action' => $controller->getActionName(),
            'actionParams' => $controller->request()->getRequestParam(),
            'general' => [
                'method' => $controller->request()->getMethod(),
                'isAjax' => $this->isAjax(),
                'isPjax' => $this->getIsPjax()
            ],
            'requestBody' => $this->getRequestBody()
        ];

        return $data;
    }

    function getRoute($controller)
    {
        return AuditEntry::getRoute($controller);
    }

    public function getFlashes()
    {
        return 1;
    }

    /**
     *
     * @param array $data
     * @return array
     */
    protected function cleanData($data)
    {
        /**
         *
         * @var $v notused;
         */
        foreach ($data as $k => $v) {
            if (in_array($k, $this->ignoreKeys)) {
                $data[$k] = null;
            }
        }

        return $data;
    }

    public function getTargetTrackerCategory()
    {}

    public function record(TrackerPoint $pt, &$records)
    {
        $records = $this->save();
    }
}
