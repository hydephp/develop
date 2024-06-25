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
        parent::__construct(rtrim(sprintf("Invalid %s in file: '%s' %s", $this->getTypeLabel($file), $file, $this->getContext($previous))), previous: $previous);
    }

    protected function getTypeLabel(string $file): string
    {
        return match (Arr::last(explode('.', $file))) {
            'md' => 'Markdown',
            'yaml', 'yml' => 'Yaml',
            'json' => 'Json',
            default => 'data',
        };
    }

    protected function getContext(?Throwable $previous): string
    {
        return ($previous && $previous->getMessage()) ? sprintf('(%s)', rtrim($previous->getMessage(), '.')) : '';
    }
}
