<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;

class BaseUrlNotSetException extends Exception
{
    public function __construct()
    {
        parent::__construct('No site URL has been set in config (or .env).');
    }
}
