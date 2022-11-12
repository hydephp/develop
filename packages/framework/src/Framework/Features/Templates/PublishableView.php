<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

use Hyde\Hyde;

abstract class PublishableView implements PublishableContract
{
    protected static string $title;
    protected static string $desc;
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
        return static::$desc;
    }

    public static function getOutputPath(): string
    {
        // All publishable views at this time are Blade templates so to
        // reduce premature complexity we just use the Blade paths here.

        return Hyde::getBladePagePath(static::$path);
    }

    public static function getPublishableData(): array
    {
        return [
            'title' => static::getTitle(),
            'description' => static::getDescription(),
        ];
    }

    protected static function getSourcePath(): string
    {
        return Hyde::vendorPath(static::$path);
    }
}
