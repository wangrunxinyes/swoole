<?php
namespace App\Module\Demo\HttpController;

use wrxswoole\Core\Http\Request;
use wrxswoole\Core\HttpController\CoreHttpController;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class Http extends CoreHttpController
{

    /**
     *
     * @Method(allow={POST,GET})
     * @Param(name="code",notEmpty={"code"})
     * @Authenticate(false)
     */
    public function data()
    {
        $code = $this->request()->getRequestParam("code");

        $this->writeSuccessJson([
            "code" => $code,
            "raw" => $this->request()
                ->__toString(),
            "request_post_data" => $this->request()
                ->getParsedBody(),
            "headers" => $this->request()
                ->getHeaders(),
            "method" => $this->request()
                ->getMethod(),
            "queryParams" => $this->request()
                ->getQueryParams()
        ]);
    }

    /**
     *
     * @Authenticate(false)
     */
    public function send()
    {
        $request = new Request('http://localhost:9501/demo/http/data?code=1');
        $request->setMethod(Request::HTTP_METHOD_POST);

        $request->addData([
            "post" => "this is post data"
        ]);

        $this->writeSuccessJson([
            "response" => $request->get()
        ]);
    }

    /**
     *
     * @Authenticate(false)
     */
    public function multipart()
    {
        $request = new Request('http://localhost:9501/demo/http/data?code=1');
        $request->setMethod(Request::HTTP_METHOD_POST);

        $request->addContent("multipart", "this is multipart data");

        $this->writeSuccessJson([
            "response" => $request->get()
        ]);
    }

    /**
     *
     * @Authenticate(false)
     */
    public function sendJson()
    {
        $request = new Request('http://localhost:9501/demo/http/data?code=1');
        $request->setPost()->setFormat(Request::FORMAT_JSON);

        $request->addData([
            "post" => "this is post data"
        ]);

        $this->writeSuccessJson([
            "response" => $request->get()
        ]);
    }
}
