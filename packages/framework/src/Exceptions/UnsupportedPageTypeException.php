<?php

namespace Hyde\Framework\Exceptions;

use Exception;

class UnsupportedPageTypeException extends Exception
{
    protected $message = 'The page type is not supported.';
    protected $code = 400;

    public function __construct(?string $page = null)
    {
        $this->message = $page ? "The page type is not supported: $page" : $this->message;

        parent::__construct($this->message, $this->code);
    }
}
