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
 * @see \Illuminate\Config\Repository
 * @see \Illuminate\Support\Facades\Config
 * @see \Hyde\Framework\Testing\Feature\TypedConfigFacadeTest
 */
class Config extends \Illuminate\Support\Facades\Config
{
    public static function getArray(array|string $key, array $default = null): array
    {
        return (array) static::get($key, $default);
    }

    public static function getString(string $key, string $default = null): string
    {
        return (string) static::get($key, $default);
    }

    public static function getInt(string $key, int $default = null): int
    {
        return (int) static::get($key, $default);
    }

    public static function getBool(string $key, bool $default = null): bool
    {
        return (bool) static::get($key, $default);
    }

    public static function getFloat(string $key, float $default = null): float
    {
        return (float) static::get($key, $default);
    }
}
