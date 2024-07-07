<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use InvalidArgumentException;

class InvalidConfigurationException extends InvalidArgumentException
{
    public function __construct(string $message = 'Invalid configuration detected.')
    {
        parent::__construct($message);
    }
}
