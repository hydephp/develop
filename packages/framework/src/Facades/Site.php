<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use Hyde\Hyde;

/**
 * Object representation for the HydePHP site and its configuration.
 *
 * @see \Hyde\Framework\Testing\Feature\SiteTest
 */
final class Site
{
    protected static string $outputPath = '_site';

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
        self::$outputPath = Hyde::pathToRelative($outputPath);
    }
}
