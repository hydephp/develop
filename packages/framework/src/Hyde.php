<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

/**
 * Encapsulates a HydePHP project, providing helpful methods for interacting with it.
 *
 * @see \Hyde\Framework\Facades\Hyde
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @link https://hydephp.com/
 */
class Hyde
{
    use Macroable;

    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

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

    /**
     * File helper methods.
     *
     * If a method uses the name `path` it refers to an internal file path.
     * if a method uses the name `link` it refers to a web link used in Blade templates.
     */

    /**
     * Get an absolute file path from a supplied relative path.
     *
     * The function returns the fully qualified path to your site's root directory.
     *
     * You may also use the function to generate a fully qualified path to a given file
     * relative to the project root directory when supplying the path argument.
     *
     * @param  string  $path
     * @return string
     */
    public static function path(string $path = ''): string
    {
        if (empty($path)) {
            return static::getBasePath();
        }

        $path = unslash($path);

        return static::getBasePath().DIRECTORY_SEPARATOR.$path;
    }

    /**
     * Works similarly to the path() function, but returns a file in the Framework package.
     *
     * @param  string  $path
     * @return string
     */
    public static function vendorPath(string $path = ''): string
    {
        return static::path('vendor/hyde/framework/'.unslash($path));
    }

    /**
     * Format a link to an HTML file, allowing for pretty URLs, if enabled.
     *
     * @see \Hyde\Framework\Testing\Unit\FileHelperPageLinkPrettyUrlTest
     */
    public static function pageLink(string $destination): string
    {
        if (config('site.pretty_urls', false) === true) {
            if (str_ends_with($destination, '.html')) {
                if ($destination === 'index.html') {
                    return '/';
                }
                if ($destination === DocumentationPage::getOutputDirectory().'/index.html') {
                    return DocumentationPage::getOutputDirectory().'/';
                }

                return substr($destination, 0, -5);
            }
        }

        return $destination;
    }

    /**
     * Inject the proper number of `../` before the links in Blade templates.
     *
     * Since v0.50.x you no longer have to supply a current page as it will be automatically retrieved from the View.
     *
     * @param  string  $destination  relative to output directory on compiled site
     * @param  string|null  $current  the current URI path relative to the site root
     * @return string
     *
     * @see \Hyde\Framework\Testing\Unit\FileHelperRelativeLinkTest
     */
    public static function relativeLink(string $destination, ?string $current = null): string
    {
        if (str_starts_with($destination, '../')) {
            return $destination;
        }

        if ($current === null) {
            $current = static::currentPage();
        }

        $nestCount = substr_count($current, '/');
        $route = '';
        if ($nestCount > 0) {
            $route .= str_repeat('../', $nestCount);
        }
        $route .= static::pageLink($destination);

        return str_replace('//', '/', $route);
    }

    /**
     * Get the current page path, or fall back to the root path.
     */
    public static function currentPage(): string
    {
        return View::shared('currentPage', '');
    }

    /**
     * Get the current page route, or fall back to null.
     */
    public static function currentRoute(): ?RouteContract
    {
        return View::shared('currentRoute');
    }

    /**
     * Gets a relative web link to the given image stored in the _site/media folder.
     * Since v0.50.x you no longer have to supply a current page as it will be automatically retrieved from the View.
     */
    public static function image(string $name, string $current = null): string
    {
        if ($current === null) {
            $current = static::currentPage();
        }

        if (str_starts_with($name, 'http')) {
            return $name;
        }

        return static::relativeLink('media/'.basename($name), $current);
    }

    /**
     * Return a qualified URI path, if SITE_URL is set in .env, else return false.
     *
     * @param  string|null  $path  optional relative path suffix. Omit to return base url.
     * @return string|false
     */
    public static function uriPath(?string $path = ''): string|false
    {
        if (config('site.url', false)) {
            return rtrim(config('site.url'), '/').'/'.(trim($path, '/') ?? '');
        }

        return false;
    }

    /**
     * Wrapper for the copy function, but allows choosing if files may be overwritten.
     *
     * @param  string  $from  The source file path.
     * @param  string  $to  The destination file path.
     * @param  bool  $force  If true, existing files will be overwritten.
     * @return bool|int Returns true|false on copy() success|failure, or an error code on failure
     */
    public static function copy(string $from, string $to, bool $force = false): bool|int
    {
        if (! file_exists($from)) {
            return 404;
        }

        if (file_exists($to) && ! $force) {
            return 409;
        }

        return copy($from, $to);
    }

    /**
     * Fluent file helper methods.
     *
     * Provides a more fluent way of getting either the absolute path
     * to a model's source directory, or an absolute path to a file within it.
     *
     * These are intended to be used as a dynamic alternative to legacy code
     * Hyde::path('_pages/foo') becomes Hyde::getBladePagePath('foo')
     */
    public static function getModelSourcePath(string $model, string $path = ''): string
    {
        if (empty($path)) {
            return static::path(DiscoveryService::getFilePathForModelClassFiles($model));
        }

        $path = unslash($path);

        return static::path(DiscoveryService::getFilePathForModelClassFiles($model).DIRECTORY_SEPARATOR.$path);
    }

    public static function getBladePagePath(string $path = ''): string
    {
        return static::getModelSourcePath(BladePage::class, $path);
    }

    public static function getMarkdownPagePath(string $path = ''): string
    {
        return static::getModelSourcePath(MarkdownPage::class, $path);
    }

    public static function getMarkdownPostPath(string $path = ''): string
    {
        return static::getModelSourcePath(MarkdownPost::class, $path);
    }

    public static function getDocumentationPagePath(string $path = ''): string
    {
        return static::getModelSourcePath(DocumentationPage::class, $path);
    }

    /**
     * Get the absolute path to the compiled site directory, or a file within it.
     */
    public static function getSiteOutputPath(string $path = ''): string
    {
        if (empty($path)) {
            return StaticPageBuilder::$outputPath;
        }

        $path = unslash($path);

        return StaticPageBuilder::$outputPath.DIRECTORY_SEPARATOR.$path;
    }

    /**
     * Decode an absolute path created with a Hyde::path() helper into its relative counterpart.
     */
    public static function pathToRelative(string $path): string
    {
        return str_starts_with($path, static::path()) ? unslash(str_replace(
            static::path(),
            '',
            $path
        )) : $path;
    }
}
