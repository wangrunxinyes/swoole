<?php
namespace wrxswoole\Core\Annotation\Tag;

use wrxswoole\Core\Annotation\Exception\MethodNotAllow;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Validator\Interfaces\ValidateInterface;
use EasySwoole\Annotation\AbstractAnnotationTag;

/**
 * Class Method
 *
 * @package EasySwoole\Http\Annotation
 * @Annotation
 */
final class Method extends AbstractAnnotationTag implements ValidateInterface
{

    /**
     *
     * @var array
     */
    public $allow = [];

    public $method = null;

    public function tagName(): string
    {
        return 'Method';
    }

    public function assetValue(?string $raw)
    {
        $str = null;
        parse_str($raw, $str);
        if (isset($str['allow'])) {
            $str = trim($str['allow'], "{}");
            $list = explode(",", $str);
            foreach ($list as $item) {
                $this->allow[] = trim(strtolower($item));
            }
        }
    }

    public function validate()
    {
        $this->method = strtolower(CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->getMethod());

        if (! in_array($this->method, $this->allow)) {
            throw new MethodNotAllow([
                "allow" => $this->allow,
                "current" => $this->method
            ], "request method is not allow");
        }
    }
}