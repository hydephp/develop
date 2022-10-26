<?php
declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;

class UnsupportedPageTypeException extends Exception
{
    /** @var string */
    protected $message = 'The page type is not supported.';

    /** @var int */
    protected $code = 400;

    public function __construct(?string $page = null)
    {
        $this->message = $page ? "The page type is not supported: $page" : $this->message;

        parent::__construct($this->message, $this->code);
    }
}
