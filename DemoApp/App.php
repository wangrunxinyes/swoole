<?php
namespace App;

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
}

?>