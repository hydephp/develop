<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Exception;
use Hyde\Hyde;

use function sprintf;

class FileNotFoundException extends Exception
{
    /** @var string */
    protected $message = 'File not found.';

    /** @var int */
    protected $code = 404;

    public function __construct(?string $path = null, ?string $customMessage = null)
    {
        parent::__construct($customMessage ?? ($path ? sprintf('File [%s] not found.', Hyde::pathToRelative($path)) : $this->message));
    }
}
