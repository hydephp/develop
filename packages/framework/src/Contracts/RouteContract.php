<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Support\Collection;

/**
 * This contract defines the methods that a Route object must implement.
 */
interface RouteContract
{
    /**
     * Construct a new Route instance for the given page model.
     *
     * @param  \Hyde\Framework\Concerns\AbstractPage  $sourceModel
     */
    public function __construct(AbstractPage $sourceModel);

    /**
     * Get the page type for the route.
     *
     * @return class-string<\Hyde\Framework\Concerns\AbstractPage>
     */
    public function getPageType(): string;

    /**
     * Get the source model for the route.
     *
     * @return \Hyde\Framework\Concerns\AbstractPage
     */
    public function getSourceModel(): AbstractPage;

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
     * Get the qualified URL for the route, using pretty URLs if enabled.
     *
     * @return string Fully qualified URL using the configured base URL.
     */
    public function getQualifiedUrl(): string;

    /**
     * Resolve a site web link to the file, using pretty URLs if enabled.
     *
     * @return string Relative URL path to the route site file.
     */
    public function getLink(): string;

    /**
     * Cast a route object into a string that can be used in a href attribute.
     * Should be the same as getLink().
     */
    public function __toString(): string;

    /**
     * Get a route from the Router index for the specified route key.
     *
     * Alias for static::getFromKey().
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Contracts\RouteContract
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function get(string $routeKey): RouteContract;

    /**
     * Get a route from the Router index for the specified route key.
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Contracts\RouteContract
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getFromKey(string $routeKey): RouteContract;

    /**
     * Get a route from the Router index for the specified source file path.
     *
     * @param  string  $sourceFilePath  Example: _posts/foo.md
     * @return \Hyde\Framework\Contracts\RouteContract
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getFromSource(string $sourceFilePath): RouteContract;

    /**
     * Get a route from the Router index for the supplied page model.
     *
     * @param  \Hyde\Framework\Concerns\AbstractPage  $page
     * @return \Hyde\Framework\Contracts\RouteContract
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getFromModel(AbstractPage $page): RouteContract;

    /**
     * Get all routes from the Router index.
     *
     * @return \Hyde\Framework\Foundation\RouteCollection<\Hyde\Framework\Contracts\RouteContract>
     */
    public static function all(): Collection;

    /**
     * Get the current route for the page being rendered.
     */
    public static function current(): RouteContract;

    /**
     * Get the home route, usually the index page route.
     */
    public static function home(): RouteContract;

    /**
     * Determine if the supplied route key exists in the route index.
     *
     * @param  string  $routeKey
     * @return bool
     */
    public static function exists(string $routeKey): bool;
}
