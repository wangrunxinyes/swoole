<?php
namespace wrxswoole\Core\Credential;

use wrxswoole\Core\Database\Model\AbstractDbModel;
use App\Db\DBConfig;
use wrxswoole\Core\Annotation\Exception\AuthenticateException;

/**
 * User AbstractDbModel model.
 *
 * @property bool $isAdmin
 * @property bool $isBlocked
 * @property bool $isConfirmed Database fields:
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $unconfirmed_email
 * @property string $password_hash
 * @property string $auth_key
 * @property string $registration_ip
 * @property integer $confirmed_at
 * @property integer $blocked_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $last_login_at
 * @property integer $flags Defined relations:
 */
class User extends AbstractDbModel
{

    /**
     *
     * @var string
     */
    protected $tableName = 'user';

    public $connectionName = DBConfig::CONNECTION_READ;

    /**
     *
     * @return boolean
     */
    function login()
    {
        return $this->validate($this->password, $this->user->password_hash);
    }

    /**
     *
     * @param string $password
     * @param string $hash
     * @throws AuthenticateException
     * @return boolean
     */
    public function validatePassword($password, $hash)
    {
        if (! is_string($password) || $password === '') {
            throw new AuthenticateException('Password must be a string and cannot be empty.');
        }

        /**
         *
         * @var $matches []
         */
        if (! preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash, $matches) || $matches[1] < 4 || $matches[1] > 30) {
            throw new AuthenticateException('Hash is invalid.');
        }

        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        }

        $test = crypt($password, $hash);
        $n = strlen($test);
        if ($n !== 60) {
            return false;
        }

        return $this->compareString($test, $hash);
    }
}
