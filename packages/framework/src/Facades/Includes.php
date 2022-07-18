<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Actions\MarkdownConverter;
use Hyde\Framework\Contracts\IncludeFacadeContract;
use Illuminate\Support\Facades\Blade;

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
        $path = static::$includesDirectory . '/' . basename($partial, '.md') . '.md';

        if (! file_exists($path)) {
            return $default;
        }

        return MarkdownConverter::parse(file_get_contents($path));
    }

    /** @inheritDoc */
    public static function blade(string $partial, ?string $default = null): ?string
    {
        $path = static::$includesDirectory . '/' . basename($partial, '.blade.php') . '.blade.php';

        if (! file_exists($path)) {
            return $default;
        }

        return Blade::render(file_get_contents($path));
    }
}