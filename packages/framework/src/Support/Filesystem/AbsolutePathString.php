<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use Hyde\Hyde;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Denotes and enforces an absolute path to a location within the project.
 */
final class AbsolutePathString extends PathString
{
    protected readonly string $value;

    public function __construct(string $value)
    {
        $this->value = Hyde::path(Hyde::pathToRelative($value));
    }

    #[ArrayShape(['absolute_path' => 'string'])]
    public function toArray(): array
    {
        return ['absolute_path' => $this->value];
    }
}
