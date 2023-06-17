<?php
namespace wrxswoole\Core\Component;

use EasySwoole\Component\Singleton;
use Composer\Autoload\ClassLoader as CoreClassLoader;

class ClassLoader extends BaseClassLoader
{

    use Singleton;

    private $hash;

    function __construct()
    {
        $this->init();
    }

    private function getFilePath($fileName)
    {
        return PROJECT_ROOT . "/vendor/" . $fileName;
    }

    private function getComposerFilePath($fileName)
    {
        return $this->getFilePath("composer/" . $fileName);
    }

    /**
     *
     * @return boolean
     */
    private function isUsingStaticLoader()
    {
        return PHP_VERSION_ID >= 50600 && ! defined('HHVM_VERSION') && (! function_exists('zend_loader_file_encoded') || ! zend_loader_file_encoded());
    }

    function getAutoLoadClassHash($filepath)
    {
        $phpcode = file_get_contents($filepath);
        $key = "return ComposerAutoloaderInit";
        $start = strpos($phpcode, $key);
        if ($start === false) {
            return false;
        }

        $start += strlen($key);
        $end = strpos($phpcode, "::getLoader();");

        if ($end === false) {
            return false;
        }

        if ($end - $start !== 32) {
            return false;
        }

        $this->hash = substr($phpcode, $start, $end - $start);
    }

    function init()
    {
        $file = $this->getFilePath('autoload.php');
        if (! $this->fileExists($file)) {
            echo "autoload.php can't be found in " . $file;
            return;
        }

        $this->getAutoLoadClassHash($file);

        if (is_null($this->hash)) {
            echo "hash can't be found.";
            return;
        }

        // return;

        /**
         *
         * @var \Composer\Autoload\ClassLoader $loader
         */
        $loader = require $file;
        $this->reRegister($loader);
    }

    function reRegister(CoreClassLoader $loader)
    {
        $loader->unregister();

        $useStaticLoader = $this->isUsingStaticLoader();
        $staticLoaderClassName = '\Composer\Autoload\ComposerStaticInit' . $this->hash;

        if ($useStaticLoader) {
            require_once $this->getComposerFilePath('/autoload_static.php');

            $this->prefixLengthsPsr4 = $staticLoaderClassName::$prefixLengthsPsr4;
            $this->prefixDirsPsr4 = $staticLoaderClassName::$prefixDirsPsr4;
            $this->prefixesPsr0 = $staticLoaderClassName::$prefixesPsr0;
            $this->classMap = $staticLoaderClassName::$classMap;
        } else {
            $map = $this->getComposerFilePath('/autoload_namespaces.php');
            foreach ($map as $namespace => $path) {
                $this->set($namespace, $path);
            }

            $map = $this->getComposerFilePath('/autoload_psr4.php');
            foreach ($map as $namespace => $path) {
                $this->setPsr4($namespace, $path);
            }

            $classMap = $this->getComposerFilePath('/autoload_classmap.php');
            if ($classMap) {
                $this->addClassMap($classMap);
            }
        }

        $this->register(true);

        if ($useStaticLoader) {
            $includeFiles = $staticLoaderClassName::$files;
        } else {
            $includeFiles = $this->getComposerFilePath('/autoload_files.php');
        }

        $func = "composerRequire" . $this->hash;

        foreach ($includeFiles as $fileIdentifier => $file) {
            if(function_exists($func))
            $func($fileIdentifier, $file);
        }
    }

    protected function findFileWithExtension($class, $ext)
    {
        // PSR-4 lookup
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $first = $class[0];
        if (isset($this->prefixLengthsPsr4[$first])) {
            $subPath = $class;
            while (false !== $lastPos = strrpos($subPath, '\\')) {
                $subPath = substr($subPath, 0, $lastPos);
                $search = $subPath . '\\';
                if (isset($this->prefixDirsPsr4[$search])) {
                    $pathEnd = DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $lastPos + 1);
                    foreach ($this->prefixDirsPsr4[$search] as $dir) {
                        if (($file = $this->fileExists($file = $dir . $pathEnd)) && $file !== false) {
                            return $file;
                        }
                    }
                }
            }
        }

        // PSR-4 fallback dirs
        foreach ($this->fallbackDirsPsr4 as $dir) {
            if (($file = $this->fileExists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) && $file !== false) {
                return $file;
            }
        }

        // PSR-0 lookup
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1) . strtr(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
        } else {
            // PEAR-like class name
            $logicalPathPsr0 = strtr($class, '_', DIRECTORY_SEPARATOR) . $ext;
        }

        if (isset($this->prefixesPsr0[$first])) {
            foreach ($this->prefixesPsr0[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (($file = $this->fileExists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) && $file !== false) {
                            return $file;
                        }
                    }
                }
            }
        }

        // PSR-0 fallback dirs
        foreach ($this->fallbackDirsPsr0 as $dir) {
            if (($file = $this->fileExists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) && $file !== false) {
                return $file;
            }
        }

        // PSR-0 include paths.
        if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr0)) {
            return $file;
        }

        return false;
    }

    function fileExists($fileName, $caseSensitive = false)
    {
        if (file_exists($fileName)) {
            return $fileName;
        }
        if ($caseSensitive)
            return false;

        // Handle case insensitive requests
        $directoryName = dirname($fileName);
        $fileArray = glob($directoryName . '/*', GLOB_NOSORT);
        $fileNameLowerCase = strtolower($fileName);
        foreach ($fileArray as $file) {
            if (strtolower($file) == $fileNameLowerCase) {
                return $file;
            }
        }
        return false;
    }
}

?>