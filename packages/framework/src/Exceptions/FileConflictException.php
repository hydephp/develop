<?php

namespace Hyde\Framework\Exceptions;

use Exception;

class FileConflictException extends Exception
{
    protected $message = 'A file already exists at this path.';
    protected $code = 409;

    public function __construct(?string $path = null)
    {
        $this->message = $path ? "File already exists: $path" : $this->message;

        parent::__construct($this->message, $this->code);
    }
}
