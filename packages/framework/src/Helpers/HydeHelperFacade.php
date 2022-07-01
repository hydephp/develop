<?php

namespace Hyde\Framework\Helpers;

use Illuminate\Support\Str;

/**
 * Provides convenient access to Hyde helpers, through the main Hyde facade.
 *
 * @see \Hyde\Framework\Testing\Feature\HydeHelperFacadeTest
 */
trait HydeHelperFacade
{
    public static function features(): Features
    {
        return new Features;
    }

    public static function hasFeature(string $feature): bool
    {
        return Features::enabled($feature);
    }

    /**
     * @since 0.44.0-beta (renamed from titleFromSlug)
     */
    public static function makeTitle(string $slug): string
    {
        return Str::headline($slug);
    }
}
