<?php

namespace Hyde\Framework\Concerns\Internal;

trait HandlesPageFilesystem
{
    /**
     * Get the directory in where source files are stored.
     *
     * @return string Path relative to the root of the project
     *
     * @example output: '_pages'
     */
    final public static function sourceDirectory(): string
    {
        return unslash(static::$sourceDirectory);
    }

    /**
     * Get the output subdirectory to store compiled HTML.
     *
     * @return string Relative to the site output directory.
     *
     * @example output: '', 'posts, 'docs
     */
    final public static function outputDirectory(): string
    {
        return unslash(static::$outputDirectory);
    }

    /**
     * Get the file extension of the source files.
     *
     * @return string (e.g. ".md")
     */
    final public static function fileExtension(): string
    {
        return '.'.ltrim(static::$fileExtension, '.');
    }

    /**
     * Qualify a page basename into a referenceable local file path.
     *
     * @param  string  $basename  for the page model source file.
     * @return string path to the file relative to project root
     *
     * @example input: MarkdownPost::qualifyFilepath('hello-world')
     * @example output: '_posts/hello-world.md'
     */
    public static function qualifyBasename(string $basename): string
    {
        return static::sourceDirectory().'/'.unslash($basename).static::fileExtension();
    }

    /**
     * Get the proper site output path for a page model.
     *
     * @param  string  $basename  for the page model source file.
     * @return string of the output file relative to the site output directory.
     *
     * @example DocumentationPage::getOutputPath('index') => 'docs/index.html'
     */
    public static function getOutputLocation(string $basename): string
    {
        // Using the trim function we ensure we don't have a leading slash when the output directory is the root directory.
        return trim(
                static::outputDirectory().'/'.unslash($basename),
                '/'
            ).'.html';
    }

    /**
     * Get the path to the source file, relative to the project root.
     * In other words, qualify the basename of the page instance.
     *
     * @return string Path relative to the project root.
     */
    public function getSourcePath(): string
    {
        return static::qualifyBasename($this->identifier);
    }

    /**
     * Get the path where the compiled page will be saved.
     *
     * @return string Path relative to the site output directory.
     */
    public function getOutputPath(): string
    {
        return $this->getRouteKey().'.html';
    }
}
