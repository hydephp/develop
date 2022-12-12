<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Stringable;

/**
 * Filesystem abstraction for a file stored in the project.
 *
 * @see \Hyde\Framework\Testing\Feature\FileTest
 */
abstract class File implements SerializableContract, Stringable
{
    use Serializable;

    /**
     * @var string The path relative to the project root.
     *
     * @example `_pages/index.blade.php`
     * @example `_media/logo.png`
     */
    public readonly string $path;

    public function __toString(): string
    {
        return $this->path;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'path' => $this->getPath(),
        ];
    }
}
