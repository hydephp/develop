<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Throwable;
use RuntimeException;
use Illuminate\Support\Arr;

use function rtrim;
use function sprintf;
use function explode;

/** @experimental This class may change significantly before its release. */
class ParseException extends RuntimeException
{
    /** @var int */
    protected $code = 422;

    public function __construct(string $file = '', ?Throwable $previous = null)
    {
        $extension = Arr::last(explode('.', $file));
        $type = match ($extension) {
            'md' => 'Markdown',
            'yaml', 'yml' => 'Yaml',
            'json' => 'Json',
            default => 'data',
        };

        $context = ($previous && $previous->getMessage()) ? sprintf('(%s)', rtrim($previous->getMessage(), '.')) : '';

        parent::__construct(rtrim(sprintf("Invalid %s in file: '%s' %s", $type, $file, $context)), previous: $previous);
    }
}
