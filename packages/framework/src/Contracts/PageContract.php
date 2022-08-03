<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Support\Collection;

interface PageContract
{
    /**
     * Get the directory in where source files are stored.
     *
     * @return string Path relative to the root of the project
     */
    public static function getSourceDirectory(): string;

    /**
     * Get the output subdirectory to store compiled HTML.
     *
     * @return string Relative to the site output directory.
     */
    public static function getOutputDirectory(): string;

    /**
     * Get the file extension of the source files.
     *
     * @return string (e.g. ".md")
     */
    public static function getFileExtension(): string;

    /**
     * Parse a source file slug into a page model.
     *
     * @param  string  $slug
     * @return static New page model instance for the parsed source file.
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelParseHelperTest
     */
    public static function parse(string $slug): PageContract;

    /**
     * Get an array of all the source file slugs for the model.
     * Essentially an alias of DiscoveryService::getAbstractPageList().
     *
     * @return array<string>|false
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelGetAllFilesHelperTest
     */
    public static function files(): array|false;

    /**
     * Get a collection of all pages, parsed into page models.
     *
     * @return \Illuminate\Support\Collection<static>
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelGetHelperTest
     */
    public static function all(): Collection;

    /**
     * Qualify a page basename into a referenceable file path.
     *
     * @param  string  $basename  for the page model source file.
     * @return string path to the file relative to project root
     */
    public static function qualifyBasename(string $basename): string;

    /**
     * Get the proper site output path for a page model.
     *
     * @param  string  $basename  for the page model source file.
     * @return string of the output file relative to the site output directory.
     *
     * @example DocumentationPage::getOutputPath('index') => 'docs/index.html'
     */
    public static function getOutputLocation(string $basename): string;

    /**
     * Get the path to the source file, relative to the project root.
     *
     * @return string Path relative to the project root.
     */
    public function getSourcePath(): string;

    /**
     * Get the path where the compiled page will be saved.
     *
     * @return string Path relative to the site output directory.
     */
    public function getOutputPath(): string;

    /**
     * Get the URI path relative to the site root.
     *
     * @example if the compiled page will be saved to _site/docs/index.html,
     *          then this method will return 'docs/index'
     *
     * @return string URI path relative to the site root.
     */
    public function getCurrentPagePath(): string;

    /**
     * Get the route for the page.
     *
     * @return \Hyde\Framework\Contracts\RouteContract
     */
    public function getRoute(): RouteContract;

    /**
     * Get the page title to display in the <head> section's <title> tag.
     *
     * @param  string|null  $title  An optional override title, so Blade templates can use the method until we implement static Blade parsing.
     * @return string Example: "Site Name - Page Title"
     */
    public function htmlTitle(?string $title = null): string;
}
