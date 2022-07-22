<?php

namespace Hyde\Framework\Helpers;

use Illuminate\Support\Str;

/**
 * Provides convenient access to Hyde helpers, through the main Hyde facade.
 *
 * @see \Hyde\Framework\Testing\Feature\HydeHelperFacadeTest
 * @deprecated Should be merged into the main Hyde class.
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

    public static function makeTitle(string $slug): string
    {
        $alwaysLowercase = ['a', 'an', 'the', 'in', 'on', 'by', 'with', 'of', 'and', 'or', 'but'];

        return ucfirst(str_ireplace(
            $alwaysLowercase,
            $alwaysLowercase,
            Str::headline($slug)
        ));
    }
}
