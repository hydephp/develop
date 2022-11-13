<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use Hyde\Hyde;
use Illuminate\Contracts\Support\Arrayable;
use JetBrains\PhpStorm\ArrayShape;
use Stringable;

/**
 * Denotes and enforces a path relative to the project root.
 */
final class RelativePathString implements Stringable, Arrayable
{
    protected readonly string $value;

    public static function make(string $value): self
    {
        return new self($value);
    }

    public function __construct(string $value)
    {
        $this->value = Hyde::pathToRelative($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    #[ArrayShape(['relative_path' => 'string'])]
    public function toArray(): array
    {
        return ['relative_path' => $this->value];
    }
}