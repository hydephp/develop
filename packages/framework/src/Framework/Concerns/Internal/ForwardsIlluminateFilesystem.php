<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Illuminate\Support\LazyCollection;
use Illuminate\Filesystem\Filesystem;

/**
 * Forwards calls to the Laravel File facade to the HydePHP Filesystem Facade.
 *
 * @interal This trait is not covered by the backward compatibility promise.
 *
 * @see \Hyde\Facades\Filesystem
 */
trait ForwardsIlluminateFilesystem
{
    /**
     * Forward calls to the Laravel File facade, but turn all paths into absolute paths.
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // Get the names of the arguments called
        $reflection = new \ReflectionMethod(Filesystem::class, $name);
        $parameters = $reflection->getParameters();
        $parameterNames = array_map(function ($parameter) {
            return $parameter->getName();
        }, $parameters);
        // Replace values for all arguments that are paths
        $arguments = array_map(function (string|array|int|bool $argumentValue, int $index) use ($parameterNames): string|array|int|bool {
            if (in_array($parameterNames[$index], ['destination', 'directory', 'file', 'firstFile', 'from', 'link', 'path', 'paths', 'pattern', 'secondFile', 'target', 'to'])) {
                if (is_string($argumentValue)) {
                    return self::absolutePath($argumentValue);
                }
                if (is_array($argumentValue)) {
                    return array_map(function ($path) {
                        return self::absolutePath($path);
                    }, $argumentValue);
                }
            }

            return $argumentValue;
        }, $arguments, array_keys($arguments));

        return forward_static_call_array([self::filesystem(), $name], $arguments);
    }
}
