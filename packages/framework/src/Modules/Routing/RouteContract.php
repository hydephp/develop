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
     * Get a route from the Router index for the specified route key.
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Modules\Routing\RouteContract|null
     */
    public static function get(string $routeKey): ?RouteContract;


    /**
     * Get a route from the Router index for the specified source file path.
     *
     * @param  string  $sourceFilePath  Example: _posts/foo.md
     * @return \Hyde\Framework\Modules\Routing\RouteContract|null
     */
    public static function getFromSource(string $sourceFilePath): ?RouteContract;

    /**
     * Get a route from the Router index for the supplied page model.
     *
     * @param \Hyde\Framework\Contracts\PageContract $page
     * @return \Hyde\Framework\Modules\Routing\RouteContract|null
     */
    public static function getFromModel(PageContract $page): ?RouteContract;
}
