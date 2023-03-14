<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Facades\Config;
use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Exceptions\BaseUrlNotSetException;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Illuminate\Support\Str;

use function substr_count;
use function file_exists;
use function str_replace;
use function str_repeat;
use function substr;
use function blank;
use function rtrim;
use function trim;

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
     * Format a web link to an HTML file, allowing for pretty URLs, if enabled.
     *
     * @see \Hyde\Framework\Testing\Unit\Foundation\HyperlinkFormatHtmlPathTest
     */
    public function formatLink(string $destination): string
    {
        if (Config::getBool('hyde.pretty_urls', false) === true) {
            if (str_ends_with($destination, '.html')) {
                if ($destination === 'index.html') {
                    return '/';
                }

                if (str_ends_with($destination, 'index.html')) {
                    return substr($destination, 0, -10);
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
     *
     * @see \Hyde\Framework\Testing\Unit\Foundation\HyperlinkFileHelperRelativeLinkTest
     */
    public function relativeLink(string $destination): string
    {
        if (str_starts_with($destination, '../')) {
            return $destination;
        }

        $nestCount = substr_count($this->kernel->currentRouteKey() ?? '', '/');
        $route = '';
        if ($nestCount > 0) {
            $route .= str_repeat('../', $nestCount);
        }
        $route .= $this->formatLink($destination);

        if (Config::getBool('hyde.pretty_urls', false) === true && $route === '/') {
            return './';
        }

        return str_replace('//', '/', $route);
    }

    /**
     * Gets a relative web link to the given file stored in the _site/media folder.
     *
     * An exception will be thrown if the file does not exist in the _media directory,
     * and the second argument is set to true.
     */
    public function mediaLink(string $destination, bool $validate = false): string
    {
        if ($validate && ! file_exists($sourcePath = "{$this->kernel->getMediaDirectory()}/$destination")) {
            throw new FileNotFoundException($sourcePath);
        }

        return $this->relativeLink("{$this->kernel->getMediaOutputDirectory()}/$destination");
    }

    /**
     * Gets a relative web link to the given image stored in the _site/media folder.
     * If the image is remote (starts with http) it will be returned as is.
     *
     * If true is passed as the second argument, and a base URL is set,
     * the image will be returned with a qualified absolute URL.
     */
    public function asset(string $name, bool $preferQualifiedUrl = false): string
    {
        if (str_starts_with($name, 'http')) {
            return $name;
        }

        $name = Str::start($name, "{$this->kernel->getMediaOutputDirectory()}/");

        if ($preferQualifiedUrl && $this->hasSiteUrl()) {
            return $this->url($name);
        }

        return $this->relativeLink($name);
    }

    /**
     * Check if a site base URL has been set in config (or .env).
     */
    public function hasSiteUrl(): bool
    {
        return ! blank(Config::getNullableString('hyde.url'));
    }

    /**
     * Return a qualified URL to the supplied path if a base URL is set.
     *
     * @param  string  $path  optional relative path suffix. Omit to return base url.
     *
     * @throws BaseUrlNotSetException If no site URL is set and no default is provided
     */
    public function url(string $path = ''): string
    {
        $path = $this->formatLink(trim($path, '/'));

        if ($this->hasSiteUrl()) {
            return rtrim(rtrim((string) Config::getNullableString('hyde.url'), '/')."/$path", '/');
        }

        throw new BaseUrlNotSetException();
    }
}
