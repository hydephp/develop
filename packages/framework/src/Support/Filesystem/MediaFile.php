<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

/**
 * File abstraction for a project media file.
 */
class MediaFile extends File
{
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'length' => $this->getContentLength(),
            'mimeType' => $this->getMimeType(),
        ]);
    }
}
