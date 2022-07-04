<?php

namespace Hyde\Framework\Modules\Routing;

class RouteNotFoundException extends \Exception
{
    protected $message = 'Route not found.';
    protected $code = 404;

    public function __construct(?string $routeKey = null)
    {
        if ($routeKey) {
            $this->message = "Route not found: '$routeKey'";
        }

        parent::__construct($this->message, $this->code);
    }
}
