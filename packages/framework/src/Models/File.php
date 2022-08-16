<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Concerns\JsonSerializesArrayable;
use Hyde\Framework\Hyde;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Filesystem abstraction for a file stored in the project.
 */
class File implements Arrayable, \JsonSerializable
{
    use JsonSerializesArrayable;

    /**
     * @var string The path relative to the project root.
     * @example `_pages/index.blade.php`
     */
    public string $path;

    /**
     * @param string $path The path relative to the project root.
     */
    public static function make(string $path): static
    {
        return new static($path);
    }

    /**
     * @param string $path The path relative to the project root.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getName(): string
    {
        return basename($this->path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getAbsolutePath(): string
    {
        return Hyde::path($this->path);
    }

    public function getContents(): string
    {
        return file_get_contents($this->path);
    }

    public function getContentLength(): int
    {
        return filesize($this->path);
    }

    public function getMimeType(): string
    {
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);

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
        ];

        if (isset($lookup[$extension])) {
            return $lookup[$extension];
        }

        if (extension_loaded('fileinfo')) {
            return mime_content_type($this->path);
        }

        return 'text/plain';
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
        ];
    }
}
