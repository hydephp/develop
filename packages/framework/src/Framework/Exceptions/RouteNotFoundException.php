<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;

use function sprintf;

class RouteNotFoundException extends Exception
{
    /** @var string */
    protected $message = 'Route not found.';

    /** @var int */
    protected $code = 404;

    public function __construct(?string $routeKey = null)
    {
        parent::__construct($routeKey ? sprintf('Route [%s] not found.', $routeKey) : $this->message);
    }

    /**
     * @interal
     *
     * @experimental
     *
     * @codeCoverageIgnore
     */
    public function setErroredFile(string $path, int $line = 0): void
    {
        $this->message = rtrim($this->message, '.').sprintf(' (in file %s)', $path);

        $this->file = realpath($path) ?: $path;
        $this->line = $line;
    }
}
