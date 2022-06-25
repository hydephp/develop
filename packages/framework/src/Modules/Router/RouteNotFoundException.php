<?php

namespace Hyde\Framework\Modules\Router;

class RouteNotFoundException extends \Exception
{
    /**
     * @param $name string The name of the route that was not found.
     */
    public function __construct(string $name)
    {
        parent::__construct("Route '$name' not found.", 404);
    }
}
