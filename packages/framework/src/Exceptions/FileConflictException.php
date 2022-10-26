<?php
declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;

class FileConflictException extends Exception
{
    /** @var string */
    protected $message = 'A file already exists at this path.';

    /** @var int */
    protected $code = 409;

    public function __construct(?string $path = null)
    {
        $this->message = $path ? "File already exists: $path" : $this->message;

        parent::__construct($this->message, $this->code);
    }
}
