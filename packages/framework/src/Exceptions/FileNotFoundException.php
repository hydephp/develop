<?php

namespace Hyde\Framework\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    protected $message = 'File not found.';
    protected $code = 404;

    public function __construct(?string $path = null)
    {
        $this->message = $path ? "File $path not found." : $this->message;

        parent::__construct($this->message, $this->code);
    }
}
