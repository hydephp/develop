<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Exceptions\BaseUrlNotSetException;
use Hyde\Framework\Foundation\Filesystem;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

/**
 * Encapsulates a HydePHP project, providing helpful methods for interacting with it.
 *
 * @see \Hyde\Framework\Hyde
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @link https://hydephp.com/
 */
class HydeKernel implements HydeKernelContract
{
    use Macroable;

    protected string $basePath;
    protected Filesystem $filesystem;

    public function __construct(?string $basePath = null)
    {
        $this->setBasePath($basePath ?? getcwd());
        $this->filesystem = new Filesystem($this->basePath);
    }

    public static function getInstance(): HydeKernelContract
    {
        return app(HydeKernelContract::class);
    }

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased';
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
    }

    // HydeHelperFacade

    public function features(): Features
    {
        return new Features;
    }

    public function hasFeature(string $feature): bool
    {
        return Features::enabled($feature);
    }

    public function makeTitle(string $slug): string
    {
        $alwaysLowercase = ['a', 'an', 'the', 'in', 'on', 'by', 'with', 'of', 'and', 'or', 'but'];

        return ucfirst(str_ireplace(
            $alwaysLowercase,
            $alwaysLowercase,
            Str::headline($slug)
        ));
    }

    /**
     * Format a link to an HTML file, allowing for pretty URLs, if enabled.
     *
     * @see \Hyde\Framework\Testing\Unit\FileHelperPageLinkPrettyUrlTest
     */
    public function formatHtmlPath(string $destination): string
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
     * @param  string  $destination  relative to output directory on compiled site
     * @return string
     *
     * @see \Hyde\Framework\Testing\Unit\FileHelperRelativeLinkTest
     */
    public function relativeLink(string $destination): string
    {
        if (str_starts_with($destination, '../')) {
            return $destination;
        }

        $nestCount = substr_count($this->currentPage(), '/');
        $route = '';
        if ($nestCount > 0) {
            $route .= str_repeat('../', $nestCount);
        }
        $route .= $this->formatHtmlPath($destination);

        return str_replace('//', '/', $route);
    }

    /**
     * Get the current page path, or fall back to the root path.
     */
    public function currentPage(): string
    {
        return View::shared('currentPage', '');
    }

    /**
     * Get the current page route, or fall back to null.
     */
    public function currentRoute(): ?RouteContract
    {
        return View::shared('currentRoute');
    }

    /**
     * Gets a relative web link to the given image stored in the _site/media folder.
     */
    public function image(string $name): string
    {
        if (str_starts_with($name, 'http')) {
            return $name;
        }

        return $this->relativeLink('media/'.basename($name));
    }

    /**
     * Return a qualified URI path, if SITE_URL is set in .env, else return false.
     *
     * @deprecated v0.53.0-beta - Use Hyde::url() or Hyde::hasSiteUrl() instead.
     *
     * @param  string  $path  optional relative path suffix. Omit to return base url.
     * @return string|false
     */
    public function uriPath(string $path = ''): string|false
    {
        if (config('site.url', false)) {
            return rtrim(config('site.url'), '/').'/'.(trim($path, '/') ?? '');
        }

        return false;
    }

    /**
     * Check if a site base URL has been set in config (or .env).
     */
    public function hasSiteUrl(): bool
    {
        return ! blank(config('site.url'));
    }

    /**
     * Return a qualified URI path to the supplied path if a base URL is set.
     *
     * @param  string  $path  optional relative path suffix. Omit to return base url.
     * @param  string|null  $default  optional default value to return if no site url is set.
     * @return string
     *
     * @throws BaseUrlNotSetException If no site URL is set and no default is provided
     */
    public function url(string $path = '', ?string $default = null): string
    {
        $path = $this->formatHtmlPath(trim($path, '/'));

        if ($this->hasSiteUrl()) {
            return rtrim(rtrim(config('site.url'), '/').'/'.($path ?? ''), '/');
        }

        if ($default !== null) {
            return $default.'/'.($path ?? '');
        }

        throw new BaseUrlNotSetException();
    }

    public function path(string $path = ''): string
    {
        return $this->filesystem->path($path);
    }

    public function vendorPath(string $path = ''): string
    {
        return $this->filesystem->vendorPath($path);
    }

    public function copy(string $from, string $to, bool $force = false): bool|int
    {
        return $this->filesystem->copy($from, $to, $force);
    }

    public function getModelSourcePath(string $model, string $path = ''): string
    {
        return $this->filesystem->getModelSourcePath($model, $path);
    }

    public function getBladePagePath(string $path = ''): string
    {
        return $this->filesystem->getBladePagePath($path);
    }

    public function getMarkdownPagePath(string $path = ''): string
    {
        return $this->filesystem->getMarkdownPagePath($path);
    }

    public function getMarkdownPostPath(string $path = ''): string
    {
        return $this->filesystem->getMarkdownPostPath($path);
    }

    public function getDocumentationPagePath(string $path = ''): string
    {
        return $this->filesystem->getDocumentationPagePath($path);
    }

    public function getSiteOutputPath(string $path = ''): string
    {
        return $this->filesystem->getSiteOutputPath($path);
    }

    public function pathToRelative(string $path): string
    {
        return $this->filesystem->pathToRelative($path);
    }
}
