<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Support\Collection;

interface PageContract
{
    /**
     * Get a value from the computed page data, or fallback to the page's front matter, then to the default value.
     *
     * @return \Hyde\Framework\Models\FrontMatter|mixed
     */
    public function get(string $key = null, mixed $default = null): mixed;

    /**
     * Get the front matter object, or a value from within.
     *
     * @return \Hyde\Framework\Models\FrontMatter|mixed
     */
    public function matter(string $key = null, mixed $default = null): mixed;

    /**
     * See if a value exists in the computed page data or the front matter.
     *
     * @param  string  $key
     * @param  bool  $strict  When set to true, an additional check if the property is not blank is performed.
     * @return bool
     */
    public function has(string $key, bool $strict = false): bool;

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
     * @since v0.59.0-beta the returned collection is a PageCollection, and now includes the source file path as the array key
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
     * Get the page model's identifier property.
     *
     * @return string The page's identifier/slug.
     */
    public function getIdentifier(): string;

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
     * @deprecated v0.50.x-dev-master May be renamed to getRouteKey()
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
     * Compile the page into static HTML.
     *
     * @return string The compiled HTML for the page.
     */
    public function compile(): string;

    /**
     * Get the page title to display in the <head> section's <title> tag.
     *
     * @return string Example: "Site Name - Page Title"
     */
    public function htmlTitle(): string;
}
