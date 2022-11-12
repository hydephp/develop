<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

use Hyde\Hyde;

abstract class PublishableView implements PublishableContract
{
    protected static string $title;
    protected static string $description;

    protected static string $path;

    public static function publish(bool $force = false): bool
    {
        $path = static::getOutputPath();

        if (file_exists($path) && ! $force) {
            return false;
        }

        return copy(static::getSourcePath(), $path);
    }

    public static function getTitle(): string
    {
        return static::$title;
    }

    public static function getDescription(): string
    {
        return static::$description;
    }

    public static function getOutputPath(): string
    {
        return static::$path;
    }

    protected static function getSourcePath(): string
    {
        return Hyde::vendorPath(static::$path);
    }
}
