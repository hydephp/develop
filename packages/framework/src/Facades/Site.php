<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use JetBrains\PhpStorm\Deprecated;

/**
 * Object representation for the HydePHP site and its configuration.
 *
 * @see \Hyde\Framework\Testing\Feature\SiteTest
 */
final class Site
{
    /**
     * The relative path to the output directory
     *
     * @deprecated This property should be made protected, and getter/setter methods should be used instead as that will ensure a consistent and predictable state.
     */
    #[Deprecated(reason: 'Use the getter/setter methods instead.', replacement: 'Site::getOutputPath()')]
    public static string $outputPath;

    public static function url(): ?string
    {
        return config('site.url');
    }

    public static function name(): ?string
    {
        return config('site.name');
    }

    public static function language(): ?string
    {
        return config('site.language');
    }

    public static function metadata(): GlobalMetadataBag
    {
        return GlobalMetadataBag::make();
    }

    public static function getOutputPath(): string
    {
        return self::$outputPath;
    }

    public static function setOutputPath(string $outputPath): void
    {
        self::$outputPath = $outputPath;
    }
}
