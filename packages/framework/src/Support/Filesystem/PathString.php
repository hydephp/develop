<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use Illuminate\Contracts\Support\Arrayable;
use Stringable;

/**
 * Base class for path strings.
 */
abstract class PathString implements Stringable, Arrayable
{
    protected readonly string $value;

    abstract public function __construct(string $value);

    final public static function make(string $value): static
    {
        return new static($value);
    }

    final public function __toString(): string
    {
        return $this->value;
    }

    final public function getValue(): string
    {
        return $this->value;
    }

    public function toRelative(): RelativePathString
    {
        return RelativePathString::make($this->value);
    }

    public function toAbsolute(): AbsolutePathString
    {
        return AbsolutePathString::make($this->value);
    }
}
