<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Concerns\Internal\FileHelpers;
use Hyde\Framework\Concerns\Internal\FluentPathHelpers;
use Hyde\Framework\Helpers\HydeHelperFacade;
use Illuminate\Support\Str;

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
    use HydeHelperFacade;

    protected static string $basePath;

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased';
    }

    public static function getBasePath(): string
    {
        if (! isset(static::$basePath)) {
            static::$basePath = getcwd();
        }

        return static::$basePath;
    }

    public static function setBasePath(string $path): void
    {
        static::$basePath = $path;
    }

    /**
     * @deprecated v0.44.0-beta use Hyde::makeTitle() instead.
     */
    public static function titleFromSlug(string $slug): string
    {
        return Str::title(str_replace('-', ' ', ($slug)));
    }
}
