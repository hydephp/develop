<?php

declare(strict_types=1);

namespace Hyde\Support\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Stringable;

/**
 * Filesystem abstraction for a file stored in the project.
 *
 * @see \Hyde\Framework\Testing\Feature\FileTest
 */
abstract class File implements Arrayable, JsonSerializable, Stringable
{
    use JsonSerializesArrayable;

    /**
     * @return string The path relative to the project root.
     */
    public function __toString(): string
    {
        return $this->path;
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'path' => $this->getPath(),
            'contents' => $this->getContents(),
            'length' => $this->getContentLength(),
            'mimeType' => $this->getMimeType(),
            'model' => $this->belongsTo,
        ];
    }

    //
}
