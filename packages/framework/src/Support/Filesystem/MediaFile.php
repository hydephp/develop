<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use function filesize;

/**
 * File abstraction for a project media file.
 */
class MediaFile extends ProjectFile
{
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'length' => $this->getContentLength(),
            'mimeType' => $this->getMimeType(),
        ]);
    }

    public function getContentLength(): int
    {
        return filesize($this->path);
    }
}
