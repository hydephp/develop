<?php

namespace Hyde\Framework;

use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Helpers\Features;
use Illuminate\Support\Facades\Facade;

/**
 * General facade for Hyde services.
 *
 * @see \Hyde\Framework\HydeKernel
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @link https://hydephp.com/
 *
 * @method static string vendorPath(string $path = '')
 * @method static string getMarkdownPagePath(string $path = '')
 * @method static string getSiteOutputPath(string $path = '')
 * @method static string getBladePagePath(string $path = '')
 * @method static string pathToRelative(string $path)
 * @method static Features features()
 * @method static bool hasFeature(string $feature)
 * @method static string path(string $path = '')
 * @method static string relativeLink(string $destination)
 * @method static string getMarkdownPostPath(string $path = '')
 * @method static bool|int copy(string $from, string $to, bool $force = false)
 * @method static string getModelSourcePath(string $model, string $path = '')
 * @method static string image(string $name)
 * @method static RouteContract|null currentRoute()
 * @method static string currentPage()
 * @method static string url(string $path = '', null|string $default = null)
 * @method static void setBasePath(string $basePath)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static string formatHtmlPath(string $destination)
 * @method static string makeTitle(string $slug)
 * @method static bool hasSiteUrl()
 * @method static string getBasePath()
 * @method static HydeKernelContract getInstance()
 * @method static string getDocumentationPagePath(string $path = '')
 * @method static bool hasMacro(string $name)
 * @method static void macro(string $name, callable|object $macro)
 * @method static void flushMacros()
 */
class Hyde extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HydeKernel::class;
    }

    public static function version(): string
    {
        return HydeKernel::version();
    }
}
