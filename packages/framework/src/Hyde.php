<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Concerns\Internal\FileHelpers;
use Hyde\Framework\Concerns\Internal\FluentPathHelpers;
use Hyde\Framework\Helpers\Features;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

/**
 * General facade for Hyde services.
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @link https://hydephp.com/
 */
class Hyde
{
    use FileHelpers;
    use FluentPathHelpers;
    use Macroable;

    protected static string $basePath;

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased';
    }

    public static function getBasePath(): string
    {
        /** @deprecated Set path in constructor when instantiating the Singleton. */
        if (! isset(static::$basePath)) {
            static::$basePath = getcwd();
        }

        return static::$basePath;
    }

    /**
     * @deprecated Set path in constructor when instantiating the Singleton.
     */
    public static function setBasePath(string $path): void
    {
        static::$basePath = $path;
    }

    // HydeHelperFacade

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
