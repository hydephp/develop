<?php

declare(strict_types=1);

namespace Hyde\Facades;

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
        if ($strict) {
            return static::get($key, $default);
        } else {
            return (array) static::get($key, $default);
        }
    }

    public static function getString(string $key, string $default = null, bool $strict = self::STRICT_DEFAULT): string
    {
        if ($strict) {
            return static::get($key, $default);
        } else {
            return (string) static::get($key, $default);
        }
    }

    public static function getInt(string $key, int $default = null, bool $strict = self::STRICT_DEFAULT): int
    {
        if ($strict) {
            return static::get($key, $default);
        } else {
            return (int) static::get($key, $default);
        }
    }

    public static function getBool(string $key, bool $default = null, bool $strict = self::STRICT_DEFAULT): bool
    {
        if ($strict) {
            return static::get($key, $default);
        } else {
            return (bool) static::get($key, $default);
        }
    }

    public static function getFloat(string $key, float $default = null, bool $strict = self::STRICT_DEFAULT): float
    {
        if ($strict) {
            return static::get($key, $default);
        } else {
            return (float) static::get($key, $default);
        }
    }
}
