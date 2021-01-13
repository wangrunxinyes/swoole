<?php
namespace wrxswoole\Core\Credential\Traits;

use Lcobucci\JWT\Token\Plain;
use wrxswoole\Core\Annotation\Exception\AuthenticateException;
use wrxswoole\Core\Annotation\Exception\InvalidTokenException;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Credential\Token;
use wrxswoole\Core\Credential\Jwt\Jwt;
use wrxswoole\Core\Trace\Tracker;

trait TokenTrait
{

    /**
     *
     * @throws InvalidTokenException
     * @return \wrxswoole\Core\Credential\Token
     */
    private function fetchToken()
    {
        $token = CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->getRequestParam("token");

        if (is_null($token)) {
            throw new InvalidTokenException([], "token can't be null.");
        }

        $config = Jwt::getInstance()->getConfig();

        $token = $config->getParser()->parse($token);

        assert($token instanceof Plain);

        $constraints = $config->getValidationConstraints();
        if (count($constraints) != 0) {
            $config->getValidator()->assert($token, ...$constraints);
        }

        $tracker = Tracker::getInstance();

        $tracker->setUser([
            "uid" => $token->claims()
                ->get("uid")
        ]);

        $tracker->setToken(new Token($token));
    }

    function validate()
    {
        try {
            $this->fetchToken();
            return true;
        } catch (\Exception $e) {
            throw new AuthenticateException([
                "type" => get_class($e),
                "error" => $e->getMessage()
            ], "failed to anthenticate[{$e->getMessage()}]");
        }
    }
}

?>