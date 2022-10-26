<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

class RouteNotFoundException extends \Exception
{
    /** @var string */
    protected $message = 'Route not found.';

    /** @var int */
    protected $code = 404;

    public function __construct(?string $routeKey = null)
    {
        if ($routeKey) {
            $this->message = "Route not found: '$routeKey'";
        }

        parent::__construct($this->message, $this->code);
    }
}
