<?php

namespace Hyde\RealtimeCompiler\Models;

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

        if ($extension === 'css') {
            return 'text/css';
        }
        if ($extension === 'js') {
            return 'application/javascript';
        }
        if ($extension === 'json') {
            return 'application/json';
        }
        if ($extension === 'html') {
            return 'text/html';
        }
        if ($extension === 'txt') {
            return 'text/plain';
        }
        if ($extension === 'md') {
            return 'text/markdown';
        }
        if ($extension === 'svg') {
            return 'image/svg+xml';
        }
        if ($extension === 'png') {
            return 'image/png';
        }
        if ($extension === 'gif') {
            return 'image/gif';
        }
        if ($extension === 'jpg' || $extension === 'jpeg') {
            return 'image/jpeg';
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
