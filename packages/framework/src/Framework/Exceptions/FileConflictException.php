<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;
use Hyde\Hyde;

class FileConflictException extends Exception
{
    /** @var string */
    protected $message = 'A file already exists at this path.';

    /** @var int */
    protected $code = 409;

    public function __construct(?string $path = null, ?string $message = null)
    {
        $this->message = $message ?? ($path ? sprintf('File [%s] already exists.', Hyde::pathToRelative($path)) : $this->message);

        parent::__construct($this->message, $this->code);
    }
}
