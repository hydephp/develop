<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

/**
 * File abstraction for a project media file.
 */
class MediaFile extends File
{
    /** @inheritDoc */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'path' => $this->getPath(),
            'length' => $this->getContentLength(),
            'mimeType' => $this->getMimeType(),
        ];
    }
}
