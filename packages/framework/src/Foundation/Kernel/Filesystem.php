<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Facades\Site;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\PharSupport;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Illuminate\Support\Collection;
use function array_map;
use function collect;
use function Hyde\normalize_slashes;
use function Hyde\path_join;
use function is_array;
use function is_string;
use function str_replace;
use function touch;
use function unlink;
use function unslash;

/**
 * File helper methods, bound to the HydeKernel instance, and is an integral part of the framework.
 *
 * All paths arguments are relative to the root of the application,
 * and will be automatically resolved to absolute paths.
 *
 * @see \Hyde\Framework\Testing\Feature\Foundation\FilesystemTest
 */
class Filesystem
{
    protected HydeKernel $kernel;

    public function __construct(HydeKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getBasePath(): string
    {
        return $this->kernel->getBasePath();
    }

    /**
     * Get an absolute file path from a supplied relative path.
     *
     * The function returns the fully qualified path to your site's root directory.
     *
     * You may also use the function to generate a fully qualified path to a given file
     * relative to the project root directory when supplying the path argument.
     */
    public function path(string $path = ''): string
    {
        if (empty($path)) {
            return $this->getBasePath();
        }

        $path = unslash($this->pathToRelative($path));

        return path_join($this->getBasePath(), $path);
    }

    /**
     * Get an absolute file path from a supplied relative path.
     *
     * Input types are matched, meaning that if the input is a string so will the output be.
     */
    public function pathToAbsolute(string|array $path): string|array
    {
        if (is_array($path)) {
            return array_map(fn (string $path): string => $this->pathToAbsolute($path), $path);
        }

        return $this->path($path);
    }

    /**
     * Decode an absolute path created with a Hyde::path() helper into its relative counterpart.
     */
    public function pathToRelative(string $path): string
    {
        return normalize_slashes(str_starts_with($path, $this->path())
            ? unslash(str_replace($this->path(), '', $path))
            : $path);
    }

    /**
     * Get the absolute path to the media source directory, or a file within it.
     */
    public function mediaPath(string $path = ''): string
    {
        if (empty($path)) {
            return $this->path(Hyde::getMediaDirectory());
        }

        $path = unslash($path);

        return $this->path(Hyde::getMediaDirectory()."/$path");
    }

    /**
     * Get the absolute path to the compiled site directory, or a file within it.
     */
    public function sitePath(string $path = ''): string
    {
        if (empty($path)) {
            return $this->path(Site::getOutputDirectory());
        }

        $path = unslash($path);

        return $this->path(Site::getOutputDirectory()."/$path");
    }

    /**
     * Get the absolute path to the compiled site's media directory, or a file within it.
     */
    public function siteMediaPath(string $path = ''): string
    {
        if (empty($path)) {
            return $this->sitePath(Hyde::getMediaOutputDirectory());
        }

        $path = unslash($path);

        return $this->sitePath(Hyde::getMediaOutputDirectory()."/$path");
    }

    /**
     * Works similarly to the path() function, but returns a file in the Framework package.
     *
     * @internal This is not intended to be used outside the HydePHP framework.
     */
    public function vendorPath(string $path = '', string $package = 'framework'): string
    {
        if (PharSupport::running() && ! PharSupport::hasVendorDirectory()) {
            return PharSupport::vendorPath($path, $package);
        }

        return $this->path("vendor/hyde/$package/".unslash($path));
    }

    /**
     * Touch one or more files in the project's directory.
     */
    public function touch(string|array $path): bool
    {
        if (is_string($path)) {
            return touch($this->path($path));
        }

        foreach ($path as $p) {
            touch($this->path($p));
        }

        return true;
    }

    /**
     * Unlink one or more files in the project's directory.
     */
    public function unlink(string|array $path): bool
    {
        if (is_string($path)) {
            return unlink($this->path($path));
        }

        foreach ($path as $p) {
            unlink($this->path($p));
        }

        return true;
    }

    /**
     * Unlink a file in the project's directory, but only if it exists.
     */
    public function unlinkIfExists(string $path): bool
    {
        if (file_exists($this->path($path))) {
            return unlink($this->path($path));
        }

        return false;
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
    public function getModelSourcePath(string $model, string $path = ''): string
    {
        if (empty($path)) {
            return $this->path(DiscoveryService::getModelSourceDirectory($model));
        }

        return $this->path(path_join(DiscoveryService::getModelSourceDirectory($model), unslash($path)));
    }

    public function getBladePagePath(string $path = ''): string
    {
        return $this->getModelSourcePath(BladePage::class, $path);
    }

    public function getMarkdownPagePath(string $path = ''): string
    {
        return $this->getModelSourcePath(MarkdownPage::class, $path);
    }

    public function getMarkdownPostPath(string $path = ''): string
    {
        return $this->getModelSourcePath(MarkdownPost::class, $path);
    }

    public function getDocumentationPagePath(string $path = ''): string
    {
        return $this->getModelSourcePath(DocumentationPage::class, $path);
    }

    public function smartGlob(string $pattern, int $flags = 0): Collection
    {
        return collect(\Hyde\Facades\Filesystem::glob($pattern, $flags))
            ->map(fn (string $path): string => $this->pathToRelative($path));
    }
}
