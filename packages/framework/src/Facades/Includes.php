<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Contracts\IncludeFacadeContract;

class Includes implements IncludeFacadeContract
{
    protected static string $includesDirectory = 'resources/_includes';

    /** @inheritDoc */
    public static function get(string $partial, ?string $default = null): ?string
    {
        $path = static::$includesDirectory . '/' . $partial;

        if (! file_exists($path)) {
            return $default;
        }

        return file_get_contents($path);
    }

    /** @inheritDoc */
    public static function markdown(string $partial, ?string $default = null): ?string
    {
        // TODO: Implement markdown() method.

        return $default;
    }

    /** @inheritDoc */
    public static function blade(string $partial, ?string $default = null): ?string
    {
        // TODO: Implement blade() method.

        return $default;
    }
}