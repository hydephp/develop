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
    public static function url(): ?string
    {
        return config('hyde.url');
    }

    public static function name(): ?string
    {
        return config('hyde.name');
    }

    public static function language(): ?string
    {
        return config('hyde.language');
    }

    public static function metadata(): GlobalMetadataBag
    {
        return GlobalMetadataBag::make();
    }

    public static function path(string $path = ''): string
    {
        return Hyde::kernel()->sitePath($path);
    }

    public static function getOutputDirectory(): string
    {
        return Hyde::kernel()->getOutputDirectory();
    }

    public static function setOutputDirectory(string $outputDirectory): void
    {
        Hyde::kernel()->setOutputDirectory($outputDirectory);
    }
}
