<?php
namespace App;

use App\Annotation\Tag\DemoAuthenticator;
use App\Component\Exception\CustomizeExceptionHandler;
use App\Component\IdentityTest;
use wrxswoole\Core\BaseApp;
use App\Module\Demo\Demo;
use EasySwoole\EasySwoole\SysConst;
use wrxswoole\Core\Component\CoreDi;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class App extends BaseApp
{

    public function storage()
    {}

    function initModules()
    {
        $this->modules = [
            Demo::getInstance()
        ];
    }

    /**
     * getComponents
     *
     * @return array
     */
    function getComponents(): array
    {
        return [
            IdentityTest::getTag() => IdentityTest::class
        ];
    }

    function getExtAnnotationTags()
    {
        return [
            DemoAuthenticator::TAG => DemoAuthenticator::class
        ];
    }

    function dependencyInjection()
    {
        parent::dependencyInjection();
        CoreDi::getInstance()->set(SysConst::ERROR_HANDLER, CustomizeExceptionHandler::class);
    }
}

?>