<?php

declare(strict_types=1);

namespace Hyde\Facades;

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
