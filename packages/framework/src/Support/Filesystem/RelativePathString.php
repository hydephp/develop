<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use Hyde\Hyde;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Denotes and enforces a path relative to the project root.
 */
final class RelativePathString extends PathString
{
    protected readonly string $value;

    public function __construct(string $value)
    {
        $this->value = Hyde::pathToRelative($value);
    }

    #[ArrayShape(['relative_path' => 'string'])]
    public function toArray(): array
    {
        return ['relative_path' => $this->value];
    }
}
