<?php

namespace Hyde\Framework;

use Illuminate\Support\Facades\Facade;
use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Helpers\Features;

/**
 * General facade for Hyde services.
 *
 * @see \Hyde\Framework\HydeKernel
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @method static string vendorPath(string $path = '')
 * @method static string getMarkdownPagePath(string $path = '')
 * @method static string getSiteOutputPath(string $path = '')
 * @method static string getBladePagePath(string $path = '')
 * @method static string pathToRelative(string $path)
 * @method static Features features()
 * @method static RouteCollection routes()
 * @method static bool hasFeature(string $feature)
 * @method static string path(string $path = '')
 * @method static string relativeLink(string $destination)
 * @method static PageCollection pages()
 * @method static string getMarkdownPostPath(string $path = '')
 * @method static bool copy(string $from, string $to)
 * @method static void boot()
 * @method static string getModelSourcePath(string $model, string $path = '')
 * @method static string image(string $name, bool $preferQualifiedUrl = false)
 * @method static RouteContract|null currentRoute()
 * @method static bool touch(array|string $path)
 * @method static string currentPage()
 * @method static string url(string $path = '', null|string $default = null)
 * @method static void setBasePath(string $basePath)
 * @method static string formatHtmlPath(string $destination)
 * @method static bool unlink(array|string $path)
 * @method static string makeTitle(string $slug)
 * @method static array toArray()
 * @method static bool hasSiteUrl()
 * @method static void setInstance(HydeKernelContract $instance)
 * @method static string getBasePath()
 * @method static HydeKernelContract getInstance()
 * @method static string getDocumentationPagePath(string $path = '')
 */
class Hyde extends Facade
{
    public static function version(): string
    {
        return HydeKernel::version();
    }

    public static function getFacadeRoot()
    {
        return HydeKernel::getInstance();
    }
}
