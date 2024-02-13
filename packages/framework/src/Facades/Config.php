<?php

declare(strict_types=1);

namespace Hyde\Facades;

use TypeError;

use function sprintf;
use function gettype;
use function call_user_func;

/**
 * An extension of the Laravel Config facade with extra
 * accessors that ensure the types of the returned values.
 *
 * @see \Illuminate\Config\Repository
 * @see \Illuminate\Support\Facades\Config
 */
class Config extends \Illuminate\Support\Facades\Config
{
    public static function getArray(string $key, array $default = null): array
    {
        return (array) self::validated(static::get($key, $default), 'array', $key);
    }

    public static function getString(string $key, string $default = null): string
    {
        return (string) self::validated(static::get($key, $default), 'string', $key);
    }

    public static function getInt(string $key, int $default = null): int
    {
        return (int) self::validated(static::get($key, $default), 'int', $key);
    }

    public static function getBool(string $key, bool $default = null): bool
    {
        return (bool) self::validated(static::get($key, $default), 'bool', $key);
    }

    public static function getFloat(string $key, float $default = null): float
    {
        return (float) self::validated(static::get($key, $default), 'float', $key);
    }

    /** @experimental Could possibly be merged by allowing null returns if default is null? Preferably with generics so the type is matched by IDE support. */
    public static function getNullableString(string $key, string $default = null): ?string
    {
        /** @var array|string|int|bool|float|null $value */
        $value = static::get($key, $default);

        if ($value === null) {
            return null;
        }

        return (string) self::validated($value, 'string', $key);
    }

    protected static function validated(mixed $value, string $type, string $key): mixed
    {
        if (! call_user_func("is_$type", $value)) {
            throw new TypeError(sprintf('%s(): Config value %s must be of type %s, %s given', __METHOD__, $key, $type, gettype($value)));
        }

        return $value;
    }
}
