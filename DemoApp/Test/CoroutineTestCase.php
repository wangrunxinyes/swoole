<?php
namespace App\Test;

use PHPUnit\Framework\TestCase;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\HttpController\TestController;

class CoroutineTestCase extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $controller = new TestController();
        $controller->beforeTest();

        CoreCoroutineThread::Start($controller);
    }
}

?>