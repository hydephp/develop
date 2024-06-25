<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Throwable;
use RuntimeException;

/** @experimental This class may change significantly before its release. */
class ParseException extends RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
