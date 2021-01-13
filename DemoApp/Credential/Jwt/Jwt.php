<?php
namespace App\Credential\Jwt;

use EasySwoole\Component\Singleton;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use DateTimeImmutable;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class Jwt
{

    use Singleton;

    /**
     *
     * @var Configuration
     */
    private $config = null;

    const DEV_KEY = 'mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw=';

    function __construct()
    {
        $this->config = Configuration::forSymmetricSigner(new Sha256(), new Key(Jwt::DEV_KEY));
    }

    /**
     *
     * @return \Lcobucci\JWT\Configuration
     */
    public function getConfig()
    {
        if (is_null($this->config)) {
            // TODO: trace error;
        }

        return $this->config;
    }

    /**
     *
     * @return \Lcobucci\JWT\Token\Plain
     */
    public function getToken($uid)
    {
        $now = new DateTimeImmutable();

        return $this->config->createBuilder()
            ->
        // Configures the issuer (iss claim)
        issuedBy('http://xgate.com')
            ->
        // Configures the audience (aud claim)
        permittedFor('http://xgate.com')
            ->
        // Configures the id (jti claim)
        identifiedBy('4f1g23a12aa')
            ->
        // Configures the time that the token was issue (iat claim)
        issuedAt($now)
            ->
        // Configures the time that the token can be used (nbf claim)
        canOnlyBeUsedAfter($now->modify('+1 minute'))
            ->
        // Configures the expiration time of the token (exp claim)
        expiresAt($now->modify('+1 hour'))
            ->
        // Configures a new claim, called "uid"
        withClaim('uid', $uid)
            ->
        // Configures a new header, called "foo"
        withHeader('foo', 'bar')
            ->
        // Builds a new token
        getToken($this->config->getSigner(), $this->config->getSigningKey());
    }
}

?>