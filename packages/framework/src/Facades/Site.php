<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Features\Metadata\GlobalMetadataBag;

/**
 * Object representation for the HydePHP site and its configuration.
 *
 * @see \Hyde\Framework\Testing\Feature\SiteTest
 */
final class Site
{
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
        return HydeKernel::getInstance()->getOutputPath();
    }

    public static function setOutputPath(string $outputPath): void
    {
        HydeKernel::getInstance()->setOutputPath($outputPath);
    }
}
