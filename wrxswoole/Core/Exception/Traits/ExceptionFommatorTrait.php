<?php
namespace wrxswoole\Core\Exception\Traits;

use wrxswoole\Core\Exception\Error\BaseException;

trait ExceptionFommatorTrait{

    static function formatExceptionTrace(\Throwable $throwable)
    {
        return [
            'exception' => get_class($throwable),
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'hint' => $throwable instanceof BaseException ? $throwable->hint : self::makeSimpleTrace($throwable)
        ];
    }

    static function makeSimpleTrace(\Throwable $throwable): array
    {
        return self::makeSimpleTraceByArray($throwable->getTrace());
    }

    static function makeSimpleTraceByArray($origin)
    {
        $trace = [];

        if (! is_array($origin)) {
            return [
                "noTrace" => [
                    'file' => $origin,
                    'line' => null,
                    'function' => null
                ]
            ];
        }

        foreach ($origin as $line) {
            if (isset($line['file'])) {
                $trace[] = [
                    'file' => $line['file'],
                    'line' => $line['line'],
                    'function' => $line['function']
                ];
            }
        }

        return $trace;
    }
}