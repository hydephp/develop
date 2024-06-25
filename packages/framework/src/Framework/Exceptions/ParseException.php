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
    /** @var int */
    protected $code = 422;

    public function __construct(string $file = '', ?Throwable $previous = null)
    {
        $extension = Arr::last(explode('.', $file));
        $type = ucfirst(str_replace(['md', 'txt', 'yml'], ['markdown', 'text', 'yaml'], $extension)) ?: 'data';

        $context = ($previous && $previous->getMessage()) ? sprintf('(%s)', rtrim($previous->getMessage(), '.')) : '';

        parent::__construct(rtrim(sprintf("Invalid %s in file: '%s' %s", $type, $file, $context)), previous: $previous);
    }
}
