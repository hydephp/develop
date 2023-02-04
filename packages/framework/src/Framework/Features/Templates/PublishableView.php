<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

use Hyde\Hyde;

/**
 * @deprecated as implementation is deprecated.
 */abstract class PublishableView implements PublishableContract
{
    protected static string $name;
    protected static string $description;
    protected static string $path;
    protected static ?string $outputPath;

    public static function publish(bool $force = false): bool
    {
        $path = static::getOutputPath();

        if (file_exists($path) && ! $force) {
            return false;
        }

        return copy(static::getSourcePath(), $path);
    }

    public static function getName(): string
    {
        return static::$name;
    }

    public static function getDescription(): string
    {
        return static::$description;
    }

    public static function getOutputPath(): string
    {
        // All publishable views at this time are Blade templates so to
        // reduce premature complexity we just use the Blade paths here.

        return Hyde::getBladePagePath(static::$outputPath ?? static::$path);
    }

    protected static function getSourcePath(): string
    {
        return Hyde::vendorPath(static::$path);
    }

    /**
     * @return array{name: string, description: string}
     */
    public static function toArray(): array
    {
        return [
            'name' => static::getName(),
            'description' => static::getDescription(),
        ];
    }
}
