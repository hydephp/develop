<?php

declare(strict_types=1);

namespace Hyde\Facades;

use TypeError;

/**
 * An extension of the Laravel Config facade with extra
 * accessors that ensure the types of the returned values.
 *
 * @internal This facade is not meant to be used by the end user.
 * @experimental This facade is experimental and may change in the future.
 *
 * @todo If class is kept internal, the facade alias should be removed from config.
 *
 * @see \Illuminate\Config\Repository
 * @see \Illuminate\Support\Facades\Config
 * @see \Hyde\Framework\Testing\Feature\TypedConfigFacadeTest
 */
class Config extends \Illuminate\Support\Facades\Config
{
    /** @var false {@todo Consider setting to true} */
    protected const STRICT_DEFAULT = false;

    public static function getArray(array|string $key, array $default = null, bool $strict = self::STRICT_DEFAULT): array
    {
        $value = static::get($key, $default);

        if ($strict && ! is_array($value)) {
            throw new TypeError(sprintf("%s(): Config value %s must be of type array, %s given", __METHOD__, $key, gettype($value)));
        }

        return (array) $value;
    }

    public static function getString(string $key, string $default = null, bool $strict = self::STRICT_DEFAULT): string
    {
        $value = static::get($key, $default);

        if ($strict && ! is_string($value)) {
            throw new TypeError(sprintf("%s(): Config value %s must be of type string, %s given", __METHOD__, $key, gettype($value)));
        }

        return (string) $value;
    }

    public static function getInt(string $key, int $default = null, bool $strict = self::STRICT_DEFAULT): int
    {
        $value = static::get($key, $default);

        if ($strict && ! is_int($value)) {
            throw new TypeError(sprintf("%s(): Config value %s must be of type int, %s given", __METHOD__, $key, gettype($value)));
        }

        return (int) $value;
    }

    public static function getBool(string $key, bool $default = null, bool $strict = self::STRICT_DEFAULT): bool
    {
        $value = static::get($key, $default);

        if ($strict && ! is_bool($value)) {
            throw new TypeError(sprintf("%s(): Config value %s must be of type bool, %s given", __METHOD__, $key, gettype($value)));
        }

        return (bool) $value;
    }

    public static function getFloat(string $key, float $default = null, bool $strict = self::STRICT_DEFAULT): float
    {
        $value = static::get($key, $default);

        if ($strict && ! is_float($value)) {
            throw new TypeError(sprintf("%s(): Config value %s must be of type float, %s given", __METHOD__, $key, gettype($value)));
        }

        return (float) $value;
    }
}
