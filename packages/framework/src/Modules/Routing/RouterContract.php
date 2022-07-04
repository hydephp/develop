<?php

namespace Hyde\Framework\Modules\Routing;

use Hyde\Framework\Contracts\PageContract;
use Illuminate\Support\Collection;

interface RouterContract
{
    /**
     * Construct a new Router instance and discover all routes.
     */
    public function __construct();

    /**
     * Get the routes discovered by the router.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoutes(): Collection;
}
