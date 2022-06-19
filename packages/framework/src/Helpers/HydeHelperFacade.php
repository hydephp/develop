<?php

namespace Hyde\Framework\Helpers;

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
}
