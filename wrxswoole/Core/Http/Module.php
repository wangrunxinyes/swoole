<?php
namespace wrxswoole\Core\Http;

use EasySwoole\Component\Singleton;

trait Module
{
    use Singleton;

    protected $httpControllerPath = "HttpController";

    public function getName()
    {
        $path = explode('\\', get_called_class());
        return strtolower(array_pop($path));
    }

    function getHttpControllerNamespace()
    {
        $namespace = (new \ReflectionObject($this))->getNamespaceName();
        $namespace .= "\\" . $this->httpControllerPath . "\\";

        return $namespace;
    }

    function createController($route)
    {
        list ($controllerId, $actionName) = $this->routing($route);
        if (is_null($controllerId)) {
            return [
                null,
                null
            ];
        }

        $controllerClassName = $this->getHttpControllerNamespace() . $controllerId;
        if (! class_exists($controllerClassName, true)) {
            echo $controllerClassName . " not existed.";
            return [
                null,
                null
            ];
        }

        return [
            $controllerClassName,
            $actionName
        ];
    }

    function routing($route)
    {
        $format = [];
        foreach ($route as $str) {
            $format[] = strtolower($str);
        }

        switch (count($format)) {
            case 0:
                return [
                    "Index",
                    "index"
                ];
                break;
            case 1:
                return [
                    array_pop($format),
                    "index"
                ];
                break;
            case 2:
                return $format;
                break;
            default:
                return [
                    null,
                    null
                ];
                break;
        }
    }
}