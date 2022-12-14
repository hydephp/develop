<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use function extension_loaded;
use function file_exists;
use function filesize;
use Hyde\Framework\Exceptions\FileNotFoundException;
use function is_file;
use function pathinfo;

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
        if (! is_file($this->path)) {
            throw new FileNotFoundException(message: "Could not get the content length of file '$this->path'");
        }

        return filesize($this->getAbsolutePath());
    }

    public function getMimeType(): string
    {
        $extension = pathinfo($this->getAbsolutePath(), PATHINFO_EXTENSION);

        // See if we can find a mime type for the extension,
        // instead of having to rely on a PHP extension.
        $lookup = [
            'txt'  => 'text/plain',
            'md'   => 'text/markdown',
            'html' => 'text/html',
            'css'  => 'text/css',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'json' => 'application/json',
            'js'   => 'application/javascript',
            'xml'  => 'application/xml',
        ];

        if (isset($lookup[$extension])) {
            return $lookup[$extension];
        }

        if (extension_loaded('fileinfo') && file_exists($this->getAbsolutePath())) {
            return mime_content_type($this->getAbsolutePath());
        }

        return 'text/plain';
    }
}
