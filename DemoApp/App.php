<?php
namespace App;

use App\Component\IdentityTest;
use wrxswoole\Core\BaseApp;
use App\Module\Demo\Demo;

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
            "test" => IdentityTest::class
        ];
    }
}

?>