<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use Stringable;

final class RelativePathString implements Stringable
{
    protected readonly string $value;

    public static function make(string $value): self
    {
        return new self($value);
    }

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
