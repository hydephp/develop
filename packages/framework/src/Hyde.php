<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Concerns\Internal\FileHelpers;
use Hyde\Framework\Concerns\Internal\FluentPathHelpers;
use Hyde\Framework\Helpers\HydeHelperFacade;
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
    use HydeHelperFacade;
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
}
