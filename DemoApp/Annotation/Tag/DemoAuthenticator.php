<?php

namespace App\Annotation\Tag;

use wrxswoole\Core\Annotation\Tag\Authenticate;
use wrxswoole\Core\HttpController\CoreHttpController;

class DemoAuthenticator extends Authenticate{
    function validate()
    {
        if(!$this->allow){
            return true;
        }

        $this->validateToken();
    }

    private function validateToken(){
        $token = CoreHttpController::getInstance()->getRequestParam("token");
        if($token == "token"){
            return true;
        }

        $this->error([
            "token" => $token
        ], 
        "failed to validate token");
    }
}