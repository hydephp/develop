<?php

namespace Hyde\Framework\Foundation;

use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\StaticPageBuilder;

/**
 * File helper methods, bound to the HydeKernel instance, and is an integral part of the framework.
 *
 * If a method uses the name `path` it refers to an internal file path.
 * if a method uses the name `link` it refers to a web link used in Blade templates.
 *
 * @see \Hyde\Framework\Testing\Feature\Foundation\FilesystemTest
 */
class Filesystem
{
    protected HydeKernelContract $kernel;

    public function __construct(HydeKernelContract $kernel)
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
     *
     * @param  string  $path
     * @return string
     */
    public function path(string $path = ''): string
    {
        if (empty($path)) {
            return $this->getBasePath();
        }

        $path = unslash($path);

        return $this->getBasePath().DIRECTORY_SEPARATOR.$path;
    }

    /**
     * Works similarly to the path() function, but returns a file in the Framework package.
     *
     * @param  string  $path
     * @return string
     */
    public function vendorPath(string $path = ''): string
    {
        return $this->path('vendor/hyde/framework/'.unslash($path));
    }

    /**
     * Wrapper for the copy function, but allows choosing if files may be overwritten.
     *
     * @param  string  $from  The source file path.
     * @param  string  $to  The destination file path.
     * @param  bool  $force  If true, existing files will be overwritten.
     * @return bool|int Returns true|false on copy() success|failure, or an error code on failure
     */
    public function copy(string $from, string $to, bool $force = false): int|bool
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
    public function getModelSourcePath(string $model, string $path = ''): string
    {
        if (empty($path)) {
            return $this->path(DiscoveryService::getFilePathForModelClassFiles($model));
        }

        $path = unslash($path);

        return $this->path(DiscoveryService::getFilePathForModelClassFiles($model).DIRECTORY_SEPARATOR.$path);
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

    /**
     * Get the absolute path to the compiled site directory, or a file within it.
     */
    public function getSiteOutputPath(string $path = ''): string
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
    public function pathToRelative(string $path): string
    {
        return str_starts_with($path, $this->path()) ? unslash(str_replace(
            $this->path(),
            '',
            $path
        )) : $path;
    }
}
