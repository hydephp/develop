<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Concerns\JsonSerializesArrayable;
use Hyde\Framework\Hyde;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * Filesystem abstraction for a file stored in the project.
 */
class File implements Arrayable, \JsonSerializable, \Stringable
{
    use JsonSerializesArrayable;

    /**
     * @var string The path relative to the project root.
     *
     * @example `_pages/index.blade.php`
     */
    public string $path;

    /**
     * If the file is associated with a page, the class can be specified here.
     *
     * @var string<\Hyde\Framework\Contracts\AbstractPage>|null
     */
    public ?string $belongsTo = null;

    /**
     * @param  string  $path  The path relative to the project root.
     */
    public static function make(string $path): static
    {
        return new static($path);
    }

    /**
     * @param  string  $path  The path relative to the project root.
     */
    public function __construct(string $path)
    {
        $this->path = Hyde::pathToRelative($path);
    }

    /**
     * @return string The path relative to the project root.
     */
    public function __toString(): string
    {
        return $this->path;
    }

    /**
     * Supply a page class to associate with this file,
     * or leave blank to get the file's associated class.
     *
     * @param  string|null  $class
     * @return string|$this|null
     */
    public function belongsTo(?string $class = null): null|string|static
    {
        if ($class) {
            $this->belongsTo = $class;

            return $this;
        }

        return $this->belongsTo;
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
            'model' => $this->belongsTo
        ];
    }

    public function withoutDirectoryPrefix(): string
    {
        return substr($this, strlen($this->belongsTo ? $this->belongsTo::$sourceDirectory : Str::before($this, '/')));
    }
}
