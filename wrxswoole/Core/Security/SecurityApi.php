<?php
namespace wrxswoole\Core\Security;

use EasySwoole\Component\Singleton;
use PhilipBrown\Signature\Auth;
use PhilipBrown\Signature\Request;
use PhilipBrown\Signature\Token;
use PhilipBrown\Signature\Guards\CheckKey;
use PhilipBrown\Signature\Guards\CheckSignature;
use PhilipBrown\Signature\Guards\CheckTimestamp;
use PhilipBrown\Signature\Guards\CheckVersion;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Credential\Component\CredentialHelper;
use PhilipBrown\Signature\Exceptions\SignatureSignatureException;
use wrxswoole\Core\Annotation\Exception\AuthenticateException;

class SecurityApi
{
    use Singleton;

    const DEFAULT_KEY = "DEFAULT_KEY";

    const DEFAULT_SECRET = "DEFAULT_SECRET";

    function auth()
    {
        $helper = new CredentialHelper();
        $helper->validate();
        $this->authenticate();
    }

    function encode($url, $data)
    {
        $token = new Token(SecurityApi::DEFAULT_KEY, SecurityApi::DEFAULT_SECRET);
        $request = new Request('POST', $url, $data);
        return $request->sign($token);
    }

    function authenticate()
    {
        $method = strtoupper(CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->getMethod());

        if ($method !== "POST") {
            return;
        }

        $server = CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->getServerParams();

        $header = CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->getHeaders();

        $pieces = [
            ((isset($server['HTTPS']) && $server['HTTPS'] !== 'off') ? "https" : "http") . "://",
            array_pop($header["host"]),
            CoreCoroutineThread::getInstance()->getCoreController()
                ->request()
                ->getUri()
                ->getPath()
        ];

        $url = implode("", $pieces);

        $data = CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->getRequestParam();

        $auth = new Auth($method, $url, $data, [
            new CheckKey(),
            new CheckVersion(),
            new CheckTimestamp(),
            new CheckSignature()
        ]);

        $token = new Token(SecurityApi::DEFAULT_KEY, SecurityApi::DEFAULT_SECRET);

        try {
            $auth->attempt($token);
        } catch (\Throwable $e) {
            throw new AuthenticateException([
                "server" => $header,
                "method" => $method,
                "url" => $url,
                "data" => $data
            ], $e->getMessage());
        }
    }
}

?>