<?php
namespace wrxswoole\Core\Annotation\Tag;

use EasySwoole\Annotation\AbstractAnnotationTag;

/**
 * Route
 *
 * @author WANG RUNXIN
 *        
 */
final class Route extends AbstractAnnotationTag
{

    const TAG = 'Route';

    /**
     *
     * @var array
     */
    public $allow = [];

    public function tagName(): string
    {
        return self::TAG;
    }

    public function assetValue(?string $raw)
    {
        parse_str($raw, $str);
        if (isset($str['match'])) {
            $str = trim($str['match'], "{}");
            $list = explode(",", $str);
            foreach ($list as $item) {
                $this->allow[] = trim($item);
            }
        }
    }
}