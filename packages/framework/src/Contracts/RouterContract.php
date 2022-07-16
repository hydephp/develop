<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Support\Collection;

interface RouterContract
{
    /**
     * Construct a new Router instance and discover all routes.
     */
    public function __construct();

    /**
     * Get the Singleton instance of the Router.
     *
     * @return \Hyde\Framework\Contracts\RouterContract
     */
    public static function getInstance(): RouterContract;

    /**
     * Get the routes discovered by the router.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Contracts\RouteContract>
     */
    public function getRoutes(): Collection;

    /**
     * Get all discovered routes for the given page class.
     *
     * @param  class-string<\Hyde\Framework\Contracts\PageContract>  $pageClass
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Contracts\RouteContract>
     */
    public function getRoutesForModel(string $pageClass): Collection;
}
