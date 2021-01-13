<?php
use App\Credential\Jwt\Jwt;
use App\Test\CoroutineTestCase;
use wrxswoole\Core\Annotation\Exception\AuthenticateException;
use wrxswoole\Core\Annotation\Tag\Authenticate;
use wrxswoole\Core\Component\CoreCoroutineThread;

final class AuthenticateTest extends CoroutineTestCase
{

    public function testCannotPassWithEmptyToken()
    {
        $this->expectException(AuthenticateException::class);

        $model = new Authenticate();
        $model->validate();
    }

    public function testCannotPassWithInvalidToken()
    {
        $this->expectException(AuthenticateException::class);

        $model = new Authenticate();

        CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->withQueryParams([
            "token" => "test"
        ]);

        $model->validate();
    }

    public function testShouldPassWithValidToken()
    {
        $token = Jwt::getInstance()->getToken("test");

        $model = new Authenticate();

        CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->withQueryParams([
            "token" => $token->__toString()
        ]);

        $model->validate();

        $this->assertTrue(true);
    }
}

?>