<?php

declare(strict_types=1);

namespace Hyde;

use Hyde\Facades\Features;
use Hyde\Foundation\FileCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\PageCollection;
use Hyde\Foundation\RouteCollection;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\HtmlString;

/**
 * General facade for Hyde services.
 *
 * @see \Hyde\Foundation\HydeKernel
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @method static string path(string $path = '')
 * @method static string vendorPath(string $path = '')
 * @method static string pathToRelative(string $path)
 * @method static string getModelSourcePath(string $model, string $path = '')
 * @method static string getBladePagePath(string $path = '')
 * @method static string getMarkdownPagePath(string $path = '')
 * @method static string getMarkdownPostPath(string $path = '')
 * @method static string getDocumentationPagePath(string $path = '')
 * @method static string sitePath(string $path = '')
 * @method static string formatLink(string $destination)
 * @method static string relativeLink(string $destination)
 * @method static string image(string $name, bool $preferQualifiedUrl = false)
 * @method static string url(string $path = '')
 * @method static string makeTitle(string $slug)
 * @method static HtmlString markdown(string $text)
 * @method static string currentPage()
 * @method static string getBasePath()
 * @method static string getSourceRoot()
 * @method static Features features()
 * @method static FileCollection files()
 * @method static PageCollection pages()
 * @method static RouteCollection routes()
 * @method static Route|null currentRoute()
 * @method static HydeKernel getInstance()
 * @method static bool hasFeature(string $feature)
 * @method static bool hasSiteUrl()
 * @method static bool copy(string $from, string $to)
 * @method static bool touch(array|string $path)
 * @method static bool unlink(array|string $path)
 * @method static void setInstance(HydeKernel $instance)
 * @method static void setBasePath(string $basePath)
 * @method static void setSourceRoot(string $sourceRoot)
 * @method static void shareViewData(HydePage $page)
 * @method static array toArray()
 * @method static void boot()
 *
 * @see \Hyde\Foundation\Concerns\ForwardsFilesystem
 * @see \Hyde\Foundation\Concerns\ForwardsHyperlinks
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
