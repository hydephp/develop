<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    /** @var string */
    protected $message = 'File not found.';

    /** @var int */
    protected $code = 404;

    public function __construct(?string $path = null, ?string $message = null)
    {
        $this->message = $message ?? ($path ? "File $path not found." : $this->message);

        parent::__construct($this->message, $this->code);
    }
}
