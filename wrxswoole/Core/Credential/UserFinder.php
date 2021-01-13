<?php
namespace wrxswoole\Core\Credential;

use wrxswoole\Core\Database\Component\Query;
use EasySwoole\Component\Singleton;

class UserFinder
{

    use Singleton;

    protected $userQuery;

    /**
     *
     * @var User
     */
    private $searchModel;

    function __construct()
    {
        $this->searchModel = User::create();
        $this->userQuery = Query::get($this->searchModel->getCurrentDbConnectionName());
    }

    /**
     * Finds a user by the given id.
     *
     * @param int $id
     *            User id to be used on search.
     *            
     * @return User
     */
    public function findUserById($id)
    {
        return $this->findUser([
            'id' => $id
        ])->one();
    }

    /**
     * Finds a user by the given username.
     *
     * @param string $username
     *            Username to be used on search.
     *            
     * @return User
     */
    public function findUserByUsername($username)
    {
        return $this->findUser([
            'username' => $username
        ])->one();
    }

    /**
     * Finds a user by the given email.
     *
     * @param string $email
     *            Email to be used on search.
     *            
     * @return User
     */
    public function findUserByEmail($email)
    {
        return $this->findUser([
            'email' => $email
        ])->one();
    }

    /**
     * Finds a user by the given username or email.
     *
     * @param string $usernameOrEmail
     *            Username or email to be used on search.
     *            
     * @return User
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * Finds a user by the given condition.
     *
     * @param mixed $condition
     *            Condition to be used on search.
     *            
     * @return Query
     */
    public function findUser($condition)
    {
        $query = clone $this->userQuery;
        return $query->where($condition);
    }
}
