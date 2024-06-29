<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;

/**
 * @deprecated This exception will be removed in v2.0.0.
 */
class BaseUrlNotSetException extends Exception
{
    /** @var string */
    protected $message = 'No site URL has been set in config (or .env).';

    /** @var int */
    protected $code = 500;
}
