<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Models\Route;

/**
 * @internal This trait is not meant to be used outside of Hyde.
 *
 * Handles the routing relations and logic for Hyde pages.
 */
trait HandlesPageRouting
{
    /**
     * Format a page identifier to a route key.
     */
    public static function routeKey(string $identifier): string
    {
        return unslash(static::outputDirectory().'/'.$identifier);
    }

    /**
     * Get the route key for the page.
     *
     * The route key is the URI path relative to the site root.
     *
     * For example, if the compiled page will be saved to _site/docs/index.html,
     * then this method will return 'docs/index'. Route keys are used to
     * identify pages, similar to how named routes work in Laravel.
     *
     * @return string The page's route key.
     */
    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    /**
     * Get the route for the page.
     *
     * @return RouteContract The page's route.
     */
    public function getRoute(): RouteContract
    {
        return new Route($this);
    }
}
