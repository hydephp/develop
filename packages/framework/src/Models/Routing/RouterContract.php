<?php

namespace Hyde\Framework\Models\Routing;

use Illuminate\Support\Collection;

interface RouterContract
{
    /**
     * Get the routes discovered by the router.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoutes(): Collection;
}
