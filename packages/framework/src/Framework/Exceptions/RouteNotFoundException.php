<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;

class RouteNotFoundException extends Exception
{
    /** @var string */
    protected $message = 'Route not found.';

    /** @var int */
    protected $code = 404;

    public function __construct(?string $routeKey = null)
    {
        $this->message = $routeKey ? sprintf('Route [%s] not found.', $routeKey) : $this->message;

        parent::__construct($this->message, $this->code);
    }
}
