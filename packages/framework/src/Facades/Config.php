<?php

declare(strict_types=1);

namespace Hyde\Facades;

use TypeError;

/**
 * An extension of the Laravel Config facade with extra
 * accessors that ensure the types of the returned values.
 *
 * @internal This facade is not (yet) meant to be used by the end user.
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

    public static function getArray(string $key, array $default = null, bool $strict = self::STRICT_DEFAULT): array
    {
        return (array) self::validated(static::get($key, $default), 'array', $key, $strict);
    }

    public static function getString(string $key, string $default = null, bool $strict = self::STRICT_DEFAULT): string
    {
        return (string) self::validated(static::get($key, $default), 'string', $key, $strict);
    }

    public static function getInt(string $key, int $default = null, bool $strict = self::STRICT_DEFAULT): int
    {
        return (int) self::validated(static::get($key, $default), 'int', $key, $strict);
    }

    public static function getBool(string $key, bool $default = null, bool $strict = self::STRICT_DEFAULT): bool
    {
        return (bool) self::validated(static::get($key, $default), 'bool', $key, $strict);
    }

    public static function getFloat(string $key, float $default = null, bool $strict = self::STRICT_DEFAULT): float
    {
        return (float) self::validated(static::get($key, $default), 'float', $key, $strict);
    }

    protected static function validated(mixed $value, string $type, string $key, bool $strict): mixed
    {
        if ($strict && ! ("is_$type")($value)) {
            throw new TypeError(sprintf('%s(): Config value %s must be of type %s, %s given', __METHOD__, $key, $type, gettype($value)));
        }

        return $value;
    }
}
