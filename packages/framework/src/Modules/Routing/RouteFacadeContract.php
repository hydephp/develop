<?php

namespace Hyde\Framework\Modules\Routing;

use Hyde\Framework\Contracts\PageContract;
use Illuminate\Support\Collection;

interface RouteFacadeContract
{
    /**
     * Get a route from the Router index for the specified route key.
     *
     * Alias for static::getFromKey().
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Modules\Routing\RouteContract|null
     */
    public static function get(string $routeKey): ?RouteContract;

    /**
     * Get a route from the Router index for the specified route key.
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Modules\Routing\RouteContract|null
     */
    public static function getFromKey(string $routeKey): ?RouteContract;

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
     * @param  \Hyde\Framework\Contracts\PageContract  $page
     * @return \Hyde\Framework\Modules\Routing\RouteContract|null
     */
    public static function getFromModel(PageContract $page): ?RouteContract;

    /**
     * Get all routes from the Router index.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Modules\Routing\RouteContract>
     */
    public static function all(): Collection;
}
