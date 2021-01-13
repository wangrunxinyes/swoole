<?php
namespace App\HttpController\Demo;

use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\HttpController\CoreHttpController;

class Index extends CoreHttpController
{

    function test()
    {
        $this->writeJson(200, [
            "test" => true
        ]);
    }

    /**
     *
     * @Authenticate(false)
     */
    function index()
    {
        $this->writeSuccessJson([
            "message" => "welcome to api.wangrunxin.com",
            "traceid" => CoreCoroutineThread::getInstance()->getCoreController()
                ->getTracker()->visitor,
            "time" => microtime()
        ]);
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (! is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }
}