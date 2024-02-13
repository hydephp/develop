<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Models;

/**
 * The File object provides an abstraction for a file,
 * with helpful methods to get information and metadata.
 */
class FileObject
{
    protected string $path;

    public function __construct(string $internalPath)
    {
        $this->path = $internalPath;
    }

    public function getStream(): string
    {
        return file_get_contents($this->path);
    }

    public function getMimeType(): string
    {
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);

        // See if we can find a mime type for the extension,
        // instead of having to rely on a PHP extension.
        $lookup = [
            'txt' => 'text/plain',
            'md' => 'text/markdown',
            'html' => 'text/html',
            'css' => 'text/css',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'json' => 'application/json',
            'js' => 'application/javascript',
        ];

        if (isset($lookup[$extension])) {
            return $lookup[$extension];
        }

        if (extension_loaded('fileinfo')) {
            return mime_content_type($this->path);
        }

        return 'text/plain';
    }

    public function getContentLength(): int
    {
        return filesize($this->path);
    }
}
