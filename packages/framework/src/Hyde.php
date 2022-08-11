<?php

namespace Hyde\Framework;

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
 * @see \Hyde\Framework\HydeKernel::vendorPath
 *
 * @method static string vendorPath(string $path = '')
 *
 * @see \Hyde\Framework\HydeKernel::getMarkdownPagePath
 *
 * @method static string getMarkdownPagePath(string $path = '')
 *
 * @see \Hyde\Framework\HydeKernel::getSiteOutputPath
 *
 * @method static string getSiteOutputPath(string $path = '')
 *
 * @see \Hyde\Framework\HydeKernel::getBladePagePath
 *
 * @method static string getBladePagePath(string $path = '')
 *
 * @see \Hyde\Framework\HydeKernel::pathToRelative
 *
 * @method static string pathToRelative(string $path)
 *
 * @see \Hyde\Framework\HydeKernel::features
 *
 * @method static \Hyde\Framework\Helpers\Features features()
 *
 * @see \Hyde\Framework\HydeKernel::routes
 *
 * @method static \Hyde\Framework\RouteCollection routes()
 *
 * @see \Hyde\Framework\HydeKernel::hasFeature
 *
 * @method static bool hasFeature(string $feature)
 *
 * @see \Hyde\Framework\HydeKernel::path
 *
 * @method static string path(string $path = '')
 *
 * @see \Hyde\Framework\HydeKernel::relativeLink
 *
 * @method static string relativeLink(string $destination)
 *
 * @see \Hyde\Framework\HydeKernel::pages
 *
 * @method static \Hyde\Framework\PageCollection pages()
 *
 * @see \Hyde\Framework\HydeKernel::getMarkdownPostPath
 *
 * @method static string getMarkdownPostPath(string $path = '')
 *
 * @see \Hyde\Framework\HydeKernel::copy
 *
 * @method static bool copy(string $from, string $to)
 *
 * @see \Hyde\Framework\HydeKernel::boot
 *
 * @method static void boot()
 *
 * @see \Hyde\Framework\HydeKernel::getModelSourcePath
 *
 * @method static string getModelSourcePath(string $model, string $path = '')
 *
 * @see \Hyde\Framework\HydeKernel::image
 *
 * @method static string image(string $name, bool $preferQualifiedUrl = false)
 *
 * @see \Hyde\Framework\HydeKernel::currentRoute
 *
 * @method static \Hyde\Framework\Contracts\RouteContract|null currentRoute()
 *
 * @see \Hyde\Framework\HydeKernel::touch
 *
 * @method static bool touch(array|string $path)
 *
 * @see \Hyde\Framework\HydeKernel::currentPage
 *
 * @method static string currentPage()
 *
 * @see \Hyde\Framework\HydeKernel::url
 *
 * @method static string url(string $path = '', null|string $default = null)
 *
 * @see \Hyde\Framework\HydeKernel::setBasePath
 *
 * @method static void setBasePath(string $basePath)
 *
 * @see \Hyde\Framework\HydeKernel::formatHtmlPath
 *
 * @method static string formatHtmlPath(string $destination)
 *
 * @see \Hyde\Framework\HydeKernel::unlink
 *
 * @method static bool unlink(array|string $path)
 *
 * @see \Hyde\Framework\HydeKernel::makeTitle
 *
 * @method static string makeTitle(string $slug)
 *
 * @see \Hyde\Framework\HydeKernel::toArray
 *
 * @method static array toArray()
 *
 * @see \Hyde\Framework\HydeKernel::hasSiteUrl
 *
 * @method static bool hasSiteUrl()
 *
 * @see \Hyde\Framework\HydeKernel::setInstance
 *
 * @method static void setInstance(\Hyde\Framework\Contracts\HydeKernelContract $instance)
 *
 * @see \Hyde\Framework\HydeKernel::getBasePath
 *
 * @method static string getBasePath()
 *
 * @see \Hyde\Framework\HydeKernel::getInstance
 *
 * @method static \Hyde\Framework\Contracts\HydeKernelContract getInstance()
 *
 * @see \Hyde\Framework\HydeKernel::getDocumentationPagePath
 *
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
