<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use RuntimeException;
use Throwable;

/**
 * @experimental Having a class like this extending an exception means it's easy to throw if enabled, however,
 *               it's not required for the build warnings feature and may be replaced by a simple array.
 */
class BuildWarning extends RuntimeException
{
    protected string $location;

    public function __construct(string $message = '', string $location = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->location = $location;
    }

    public function getLocation(): string
    {
        return $this->location;
    }
}
