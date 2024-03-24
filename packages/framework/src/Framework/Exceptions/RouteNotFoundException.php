<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;

use function sprintf;

class RouteNotFoundException extends Exception
{
    /** @var string */
    protected $message = 'PageRoute not found.';

    /** @var int */
    protected $code = 404;

    public function __construct(?string $routeKey = null)
    {
        parent::__construct($routeKey ? sprintf('PageRoute [%s] not found.', $routeKey) : $this->message);
    }
}
