<?php
namespace wrxswoole\Core\Credential;

use Lcobucci\JWT\Token\Plain;
use wrxswoole\Core\Component\CoreCoroutineThread;

class Token
{

    const TOKEN_INSTANCE = "TOKEN_INSTANCE";

    const TOKEN_CLASS = "TOKEN_CLASS";

    /**
     *
     * @var Plain
     */
    private $token = null;

    function __construct(Plain $token)
    {
        $this->token = $token;
    }

    /**
     *
     * @return \wrxswoole\Core\Credential\Token
     */
    static function getInstance()
    {
        return CoreCoroutineThread::getInstance()->getToken();
    }

    public function getUid()
    {
        return $this->token->claims()->get("uid");
    }
}

?>