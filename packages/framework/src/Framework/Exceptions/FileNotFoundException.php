<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;
use Hyde\Hyde;

class FileNotFoundException extends Exception
{
    /** @var string */
    protected $message = 'File not found.';

    /** @var int */
    protected $code = 404;

    public function __construct(?string $path = null, ?string $message = null)
    {
        $this->message = $message ?? ($path ? sprintf('File [%s] not found.', Hyde::pathToRelative($path)) : $this->message);

        parent::__construct($this->message, $this->code);
    }
}
