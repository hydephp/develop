<?php

namespace Hyde\Framework\Foundation;

use Hyde\Framework\Exceptions\BaseUrlNotSetException;
use Hyde\Framework\HydeKernel;
use Hyde\Framework\Models\Pages\DocumentationPage;

/**
 * Contains helpers and logic for resolving web paths for compiled files.
 *
 * It's bound to the HydeKernel instance, and is an integral part of the framework.
 */
class Hyperlinks
{
    protected HydeKernel $kernel;

    public function __construct(HydeKernel $kernel)
    {
        $this->kernel = $kernel;
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

        $nestCount = substr_count($this->kernel->currentPage(), '/');
        $route = '';
        if ($nestCount > 0) {
            $route .= str_repeat('../', $nestCount);
        }
        $route .= $this->formatHtmlPath($destination);

        return str_replace('//', '/', $route);
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
}
