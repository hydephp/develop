<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Throwable;
use RuntimeException;
use Illuminate\Support\Arr;

use function rtrim;
use function sprintf;
use function explode;
use function ucfirst;
use function str_replace;

/** @experimental This class may change significantly before its release. */
class ParseException extends RuntimeException
{
    public function __construct(string $file = '', ?Throwable $previous = null)
    {
        $extension = Arr::last(explode('.', $file));
        $type = ucfirst(str_replace(['md', 'yml'], ['markdown', 'yaml'], $extension));

        parent::__construct(sprintf("Invalid %s in file: '%s' (%s)", $type, $file, rtrim($previous->getMessage(), '.')), previous: $previous);
    }
}
