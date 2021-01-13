<?php
namespace App\Module\Demo\HttpController;

use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\HttpController\CoreHttpController;
use wrxswoole\Core\Log\Logger;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class Log extends CoreHttpController
{

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function index()
    {
        $this->log("access log/index");
        $this->writeSuccessJson([
            "message" => "welcome to api.wangrunxin.com",
            "traceid" => CoreCoroutineThread::getInstance()->getCoreController()
                ->getTracker()->visitor,
            "time" => microtime()
        ]);
        $this->log("end log/index");
    }

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function logonly()
    {
        Logger::getInstance()->info("access log/logonly");

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
    public function throwerror()
    {
        $this->log("access log/throwerror");
        $this->error("throw an error");
    }

    /**
     *
     * @Method(allow={GET})
     * @Authenticate(false)
     */
    public function break()
    {
        $this->log("access log/break");
        $this->breakRequest("just end this request");
    }
}
