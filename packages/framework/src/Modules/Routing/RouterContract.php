<?php

namespace Hyde\Framework\Modules\Routing;

use Hyde\Framework\Contracts\PageContract;
use Illuminate\Support\Collection;

interface RouterContract
{
    /**
     * Get the routes discovered by the router.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoutes(): Collection;

    /**
     * Construct a new Route instance for the given page model and add it to the router.
     *
     * @param \Hyde\Framework\Contracts\PageContract $page
     * @return $this<\Hyde\Framework\Modules\Routing\RouteContract>
     */
    public function discover(PageContract $page): self;
}
