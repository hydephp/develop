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
        $type = match (Arr::last(explode('.', $file))) {
            'md' => 'Markdown',
            'yaml', 'yml' => 'Yaml',
            'json' => 'Json',
            default => 'data',
        };

        $context = $this->getContext($previous);

        parent::__construct(rtrim(sprintf("Invalid %s in file: '%s' %s", $type, $file, $context)), previous: $previous);
    }

    protected function getContext(?Throwable $previous): string
    {
        return ($previous && $previous->getMessage()) ? sprintf('(%s)', rtrim($previous->getMessage(), '.')) : '';
    }
}
