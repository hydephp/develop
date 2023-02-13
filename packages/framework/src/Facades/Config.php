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
    protected const STRICT_DEFAULT = false;

    public static function getArray(array|string $key, array $default = null, bool $strict = self::STRICT_DEFAULT): array
    {
        return $strict ? static::get($key, $default) : (array) static::get($key, $default);
    }

    public static function getString(string $key, string $default = null, bool $strict = self::STRICT_DEFAULT): string
    {
        return $strict ? static::get($key, $default) : (string) static::get($key, $default);
    }

    public static function getInt(string $key, int $default = null, bool $strict = self::STRICT_DEFAULT): int
    {
        return $strict ? static::get($key, $default) : (int) static::get($key, $default);
    }

    public static function getBool(string $key, bool $default = null, bool $strict = self::STRICT_DEFAULT): bool
    {
        return $strict ? static::get($key, $default) : (bool) static::get($key, $default);
    }

    public static function getFloat(string $key, float $default = null, bool $strict = self::STRICT_DEFAULT): float
    {
        return $strict ? static::get($key, $default) : (float) static::get($key, $default);
    }
}
