<?php

declare(strict_types=1);

namespace Hyde\Framework\Foundation;

use Hyde\Framework\Exceptions\BaseUrlNotSetException;
use Hyde\Framework\HydeKernel;
use Hyde\Framework\Models\Pages\DocumentationPage;

/**
 * Contains helpers and logic for resolving web paths for compiled files.
 *
 * It's bound to the HydeKernel instance, and is an integral part of the framework.
 *
 * @see \Hyde\Framework\Testing\Feature\Foundation\HyperlinksTest
 */
class Hyperlinks
{
    protected HydeKernel $kernel;

    public function __construct(HydeKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Format a web link to an HTML file, allowing for pretty URLs, if enabled.
     *
     * @see \Hyde\Framework\Testing\Unit\Foundation\HyperlinkformatLinkTest
     */
    public function formatLink(string $destination): string
    {
        if (config('site.pretty_urls', false) === true) {
            if (str_ends_with($destination, '.html')) {
                if ($destination === 'index.html') {
                    return '/';
                }

                if ($destination === DocumentationPage::outputDirectory().'/index.html') {
                    return DocumentationPage::outputDirectory().'/';
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
     * @see \Hyde\Framework\Testing\Unit\Foundation\HyperlinkFileHelperRelativeLinkTest
     */
    public function relativeLink(string $destination): string
    {
        if (str_starts_with($destination, '../')) {
            return $destination;
        }

        $nestCount = substr_count($this->kernel->currentPage() ?? '', '/');
        $route = '';
        if ($nestCount > 0) {
            $route .= str_repeat('../', $nestCount);
        }
        $route .= $this->formatLink($destination);

        return str_replace('//', '/', $route);
    }

    /**
     * Gets a relative web link to the given image stored in the _site/media folder.
     * If the image is remote (starts with http) it will be returned as is.
     *
     * If true is passed as the second argument, and a base URL is set,
     * the image will be returned with a qualified absolute URL.
     */
    public function image(string $name, bool $preferQualifiedUrl = false): string
    {
        if (str_starts_with($name, 'http')) {
            return $name;
        }

        if ($preferQualifiedUrl && $this->hasSiteUrl()) {
            return $this->url('media/'.basename($name));
        }

        return $this->relativeLink('media/'.basename($name));
    }

    /**
     * Check if a site base URL has been set in config (or .env).
     */
    public function hasSiteUrl(): bool
    {
        return ! blank(config('site.url'));
    }

    /**
     * Return a qualified URL to the supplied path if a base URL is set.
     *
     * @param  string  $path  optional relative path suffix. Omit to return base url.
     * @return string
     *
     * @throws BaseUrlNotSetException If no site URL is set and no default is provided
     */
    public function url(string $path = ''): string
    {
        $path = $this->formatLink(trim($path, '/'));

        if ($this->hasSiteUrl()) {
            return rtrim(rtrim(config('site.url'), '/')."/$path", '/');
        }

        throw new BaseUrlNotSetException();
    }
}
