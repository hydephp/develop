<?php

declare(strict_types=1);

namespace Hyde;

use Hyde\Facades\Features;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;
use Hyde\Support\Filesystem\SourceFile;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\HtmlString;
use JetBrains\PhpStorm\Pure;

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
 * @method static string vendorPath(string $path = '', string $package = 'framework')
 * @method static string pathToAbsolute(string $path)
 * @method static string pathToRelative(string $path)
 * @method static string sitePath(string $path = '')
 * @method static string mediaPath(string $path = '')
 * @method static string siteMediaPath(string $path = '')
 * @method static string formatLink(string $destination)
 * @method static string relativeLink(string $destination)
 * @method static string mediaLink(string $destination, bool $validate = false)
 * @method static string asset(string $name, bool $preferQualifiedUrl = false)
 * @method static string url(string $path = '')
 * @method static string makeTitle(string $value)
 * @method static string normalizeNewlines(string $string)
 * @method static string stripNewlines(string $string)
 * @method static string trimSlashes(string $string)
 * @method static HtmlString markdown(string $text, bool $stripIndentation = false)
 * @method static string currentPage()
 * @method static string getBasePath()
 * @method static string getSourceRoot()
 * @method static string getOutputDirectory()
 * @method static string getMediaDirectory()
 * @method static string getMediaOutputDirectory()
 * @method static Features features()
 * @method static FileCollection<SourceFile> files()
 * @method static PageCollection<HydePage> pages()
 * @method static RouteCollection<Route> routes()
 * @method static Route|null currentRoute()
 * @method static HydeKernel getInstance()
 * @method static Filesystem filesystem()
 * @method static array getRegisteredExtensions()
 * @method static bool hasFeature(string $feature)
 * @method static bool hasSiteUrl()
 * @method static void setInstance(HydeKernel $instance)
 * @method static void setBasePath(string $basePath)
 * @method static void setOutputDirectory(string $outputDirectory)
 * @method static void setMediaDirectory(string $mediaDirectory)
 * @method static void setSourceRoot(string $sourceRoot)
 * @method static void shareViewData(HydePage $page)
 * @method static array toArray()
 * @method static bool isBooted()
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

    public static function getFacadeRoot(): HydeKernel
    {
        return HydeKernel::getInstance();
    }

    #[Pure]
    public static function kernel(): HydeKernel
    {
        return HydeKernel::getInstance();
    }
}
