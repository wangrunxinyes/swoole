<?php
namespace App\HttpController\Security;

use App\Credential\Token;
use App\Credential\Jwt\Jwt;
use App\Model\Customer;
use wrxswoole\Core\HttpController\CoreHttpController;

class User extends CoreHttpController
{

    /**
     *
     * @Method(allow={POST,GET})
     * @Param(name="code",notEmpty={"code"})
     * @Authenticate(false)
     */
    public function login()
    {
        $code = $this->request()->getRequestParam("code");
        $model = Customer::create()->get([
            "code" => $code
        ]);

        if (! $model) {
            return $this->writeFailedJson([
                "msg" => "can't find target customer",
                "code" => $code
            ]);
        }
        $model->update_at = time();

        $model->update();

        $token = Jwt::getInstance()->getToken($model->id);

        $this->writeSuccessJson([
            "token" => $token->__toString()
        ]);
    }

    /**
     *
     * @Method(allow={GET,POST})
     * @\EasySwoole\Http\Annotation\Param(name="type",inArray="{1,2,3,4}",notEmpty={"type"})
     * @Route(match={private-access})
     */
    public function private()
    {
        $customer = Customer::create()->get(Token::getInstance()->getUid());

        $this->writeSuccessJson([
            "customer_id" => $customer->id,
            "customer_code" => $customer->code,
            "msg" => "you can visit private asset now.",
            "type" => $this->request()
                ->getRequestParam("type")
        ]);
    }
}