<?php
namespace App\Credential;

use EasySwoole\Component\Di;
use Lcobucci\JWT\Token\Plain;

class Token
{

    const TOKEN_INSTANCE = "TOKEN_INSTANCE";

    /**
     *
     * @var Plain
     */
    private $token = null;

    function __construct(Plain $token)
    {
        $this->token = $token;
        Di::getInstance()->set(Token::TOKEN_INSTANCE, $this);
    }

    /**
     *
     * @return Token
     */
    public static function getInstance()
    {
        return Di::getInstance()->get(Token::TOKEN_INSTANCE);
    }

    public function getUid()
    {
        return $this->token->claims()->get("uid");
    }
}

?>