<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Framework\Features\Metadata\GlobalMetadataBag;

/**
 * Facade to quickly get data for the HydePHP site and its configuration.
 */
class Site
{
    public static function url(): ?string
    {
        return Config::getNullableString('hyde.url');
    }

    public static function name(): ?string
    {
        return Config::getNullableString('hyde.name');
    }

    public static function language(): ?string
    {
        return Config::getNullableString('hyde.language');
    }

    public static function metadata(): GlobalMetadataBag
    {
        return GlobalMetadataBag::make();
    }
}
