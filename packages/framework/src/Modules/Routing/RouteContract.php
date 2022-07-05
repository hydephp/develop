<?php

namespace Hyde\Framework\Modules\Routing;

use Hyde\Framework\Contracts\PageContract;

interface RouteContract
{
    /**
     * Construct a new Route instance for the given page model.
     *
     * @param  \Hyde\Framework\Contracts\PageContract  $sourceModel
     */
    public function __construct(PageContract $sourceModel);

    /**
     * Get the page type for the route.
     *
     * @return class-string<\Hyde\Framework\Contracts\PageContract>
     */
    public function getPageType(): string;

    /**
     * Get the source model for the route.
     *
     * @return \Hyde\Framework\Contracts\PageContract
     */
    public function getSourceModel(): PageContract;

    /**
     * Get the unique route key for the route.
     *
     * @return string The route key. Generally <output-directory/slug>.
     */
    public function getRouteKey(): string;

    /**
     * Get the path to the source file.
     *
     * @return string Path relative to the root of the project.
     */
    public function getSourceFilePath(): string;

    /**
     * Get the path to the output file.
     *
     * @return string Path relative to the site output directory.
     */
    public function getOutputFilePath(): string;

    /**
     * Resolve a site web link to the file, using pretty URLs if enabled.
     *
     * @param string $currentPage The current page path, or blank to get use the site root as base.
     * @return string Relative path to the page
     */
    public function getLink(string $currentPage = ''): string;
}
