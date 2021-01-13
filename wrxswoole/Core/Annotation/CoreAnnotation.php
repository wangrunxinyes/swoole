<?php
namespace wrxswoole\Core\Annotation;

use EasySwoole\Annotation\Annotation;
use wrxswoole\Core\Annotation\Tag\Authenticate;
use EasySwoole\Annotation\AbstractAnnotationTag;

/**
 *
 * editable annotaion creator;
 *
 * @author WANG RUNXIN
 *        
 */
class CoreAnnotation extends Annotation
{

    const ERROR = "CoreAnnotation_ERROR";

    function getClassMethodAnnotation(\ReflectionMethod $method): array
    {
        $doc = $method->getDocComment();
        $doc = $doc ? $doc : '';
        $annotations = $this->parser($doc);

        if (! isset($annotations[Authenticate::TAG])) {
            $annotations[Authenticate::TAG][] = new Authenticate();
        }

        return $annotations;
    }

    private function parser(string $doc): array
    {
        $result = [];
        $tempList = explode(PHP_EOL, $doc);
        foreach ($tempList as $line) {
            $line = trim($line);
            $pos = strpos($line, '@');
            if ($pos !== false && $pos <= 3) {
                $lineItem = self::parserLine($line);
                if ($lineItem) {
                    $tagName = '';
                    if (isset($this->parserTagList[strtolower($lineItem->getName())])) {
                        $tagName = $lineItem->getName();
                    } else if (isset($this->aliasMap[md5(strtolower($lineItem->getName()))])) {
                        $tagName = $this->aliasMap[md5(strtolower($lineItem->getName()))];
                        $lineItem->setName($tagName);
                    }
                    if (isset($this->parserTagList[strtolower($tagName)])) {
                        /** @var AbstractAnnotationTag $obj */
                        $obj = clone $this->parserTagList[strtolower($tagName)];
                        $obj->assetValue($lineItem->getValue());
                        $result[$lineItem->getName()][] = $obj;
                    } else if ($this->strictMode) {
                        throw new \Exception("parser fail because of unregister tag name:{$lineItem->getName()} in strict parser mode");
                    }
                } else if ($this->strictMode) {
                    throw new \Exception("parser fail for data:{$line} in strict parser mode");
                }
            }
        }
        return $result;
    }
}

?>