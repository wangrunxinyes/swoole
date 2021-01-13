<?php
namespace App\Module\Demo\HttpController;

use App\Module\Demo\Model\SubClass;
use EasySwoole\Component\WaitGroup;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Database\Traits\DbTrait;
use wrxswoole\Core\Exception\Error\Notice;
use wrxswoole\Core\HttpController\CoreHttpController;
use wrxswoole\Core\Model\ContextSingleTonModel;
use wrxswoole\Core\Model\CoroutineSingleTonModel;
use wrxswoole\Core\Model\DiSingleTonModel;
use wrxswoole\Core\Model\SingleTonModel;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class Base extends CoreHttpController
{

    use DbTrait;

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function index()
    {
        $this->writeSuccessJson([
            "message" => "welcome to api.wangrunxin.com",
            "traceid" => CoreCoroutineThread::getInstance()->getCoreController()
                ->getTracker()->visitor,
            "time" => microtime()
        ]);
    }

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function private()
    {
        $class = new SubClass();
        $class->change();
        $result = [
            [
                "private" => $class->getPrivate(),
                "protect" => $class->getProtect()
            ]
        ];

        $class->changeSub();

        $result[] = [
            "private" => $class->getPrivate(),
            "protect" => $class->getProtect()
        ];

        $this->writeSuccessJson($result);
    }

    /**
     *
     * @Method(allow={GET,POST})
     * @Authenticate(false)
     */
    public function singleton()
    {
        $data = [];

        $wait = new WaitGroup();

        $models = [
            SingleTonModel::class,
            ContextSingleTonModel::class,
            CoroutineSingleTonModel::class,
            DiSingleTonModel::class
        ];

        foreach ($models as $className) {

            $model = $className::getInstance();
            if (is_null($model)) {
                throw new Notice([
                    "classname" => $className
                ]);
            }
            $data[$className]["original_value"] = $model->getKey();
            $model->setKey("new_value");
            $data[$className]["set_value"] = $model->getKey();

            $wait->add();
            go(function () use (&$data, $wait, $className) {
                $model = $className::getInstance();
                $data[$className]["coroutine_value"] = $model->getKey();
                $wait->done();
            });
        }

        $wait->wait();

        $this->writeSuccessJson($data);
    }

    /**
     *
     * @Method(allow={POST,GET})
     * @Param(name="code",notEmpty={"code"})
     * @Authenticate(false)
     */
    public function data()
    {
        $code = $this->request()->getRequestParam("code");

        $this->writeSuccessJson([
            "code" => $code
        ]);
    }

    /**
     *
     * @Authenticate(false)
     */
    public function testDb()
    {
        $sql = "insert into `test`(`key`, `time`) values('a', :time_a), ('b', :time_b)";
        $data = $this->fetch($sql, [
            ":time_a" => time(),
            ":time_b" => time() + 1
        ]);

        $this->writeSuccessJson($data);
    }
}
