<?php
namespace wrxswoole\Core\Annotation\Tag;

use EasySwoole\Annotation\AbstractAnnotationTag;
use EasySwoole\Component\Di;
use Lcobucci\JWT\Token\Plain;
use wrxswoole\Core\Annotation\Exception\AuthenticateException;
use wrxswoole\Core\Annotation\Exception\InvalidTokenException;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Component\CoreDi;
use wrxswoole\Core\Credential\Token;
use wrxswoole\Core\Credential\Jwt\Jwt;
use wrxswoole\Core\Trace\Tracker;
use wrxswoole\Core\Validator\ValidatorTrait;
use wrxswoole\Core\Validator\Interfaces\ValidateInterface;
use wrxswoole\Core\Credential\Component\CredentialHelper;
use wrxswoole\Core\Security\SecurityApi;

/**
 * Route
 *
 * @author WANG RUNXIN
 *        
 */
final class Authenticate extends AbstractAnnotationTag implements ValidateInterface
{
    use ValidatorTrait;

    const ANONYMOUS = 'anonymous';

    const TAG = 'Authenticate';

    /**
     *
     * @var bool
     */
    public $allow = true;

    public $token = null;

    public $className = Token::class;

    function __construct()
    {
        if (! is_null(Di::getInstance()->get(Token::TOKEN_CLASS))) {
            $this->className = CoreDi::getInstance()->get(Token::TOKEN_CLASS);
        }
    }

    public function tagName(): string
    {
        return self::TAG;
    }

    public function assetValue(?string $raw)
    {
        if (strtolower($raw) === "false") {
            return $this->allow = false;
        }
    }

    public function validate()
    {
        if (! $this->allow) {
            return true;
        }

        SecurityApi::getInstance()->auth();

        return true;
    }
}