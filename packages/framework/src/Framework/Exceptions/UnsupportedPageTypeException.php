<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;

use function sprintf;

class UnsupportedPageTypeException extends Exception
{
    /** @var string */
    protected $message = 'The page type is not supported.';

    /** @var int */
    protected $code = 400;

    public function __construct(?string $page = null)
    {
        parent::__construct($page ? sprintf('The page type [%s] is not supported.', $page) : $this->message);
    }
}
