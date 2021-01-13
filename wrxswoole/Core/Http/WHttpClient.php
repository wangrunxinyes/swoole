<?php
namespace wrxswoole\Core\Http;

use EasySwoole\HttpClient\HttpClient;

class WHttpClient extends HttpClient
{

    /**
     * 默认请求头
     *
     * @var array
     */
    protected $header = [
        'accept' => '*/*',
        'pragma' => 'no-cache',
        'cache-control' => 'no-cache'
    ];

    /**
     * 设置单个请求头
     * 根据 RFC 请求头不区分大小写 会全部转成小写
     *
     * @param string $key
     * @param string $value
     * @param
     *            bool strtolower
     * @return HttpClient
     */
    public function setHeader(string $key, string $value, $strtolower = true): HttpClient
    {
        $this->header[$key] = $value;
        return $this;
    }

    /**
     * 设置请求头集合
     *
     * @param array $header
     * @param bool $isMerge
     * @param
     *            bool strtolower
     * @return HttpClient
     */
    public function setHeaders(array $header, $isMerge = true, $strtolower = true): HttpClient
    {
        if (empty($header)) {
            return $this;
        }

        // 非合并模式先清空当前的Header再设置
        if (! $isMerge) {
            $this->header = [];
        }

        foreach ($header as $name => $value) {
            $this->setHeader($name, $value, $strtolower);
        }
        return $this;
    }
}

?>