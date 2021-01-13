<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace wrxswoole\Core\Http\Formatter;

use EasySwoole\Component\Singleton;
use wrxswoole\Core\Http\Request;

/**
 * JsonFormatter formats HTTP message as JSON.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class JsonFormatter implements FormatterInterface
{

    use Singleton;

    /**
     *
     * @var int the encoding options. For more details please refer to
     *      <http://www.php.net/manual/en/function.json-encode.php>.
     */
    public $encodeOptions = 0;

    /**
     *
     * {@inheritdoc}
     */
    public function format(Request $request)
    {
        $request->addHeader('Content-Type', 'application/json; charset=UTF-8');
        if (($data = $request->getData()) !== null) {
            $content = json_encode($data);
            $request->setContent($content);
            $request->addHeader('Content-Length', strlen($content));
        }
        return $request;
    }
}