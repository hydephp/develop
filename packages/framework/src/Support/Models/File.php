<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Hyde;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Stringable;

/**
 * @deprecated Is split into a new class structure.
 *
 * Filesystem abstraction for a file stored in the project.
 * @see \Hyde\Framework\Testing\Feature\FileTest
 */
class File implements SerializableContract, Stringable
{
    use Serializable;

    /**
     * @var string The path relative to the project root.
     *
     * @example `_pages/index.blade.php`
     */
    public string $path;

    /**
     * If the file is associated with a page, the class can be specified here.
     *
     * @var class-string<\Hyde\Pages\Concerns\HydePage>|null
     */
    public ?string $belongsTo = null;

    /**
     * @param  string  $path  The path relative to the project root.
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $belongsToClass
     * @return \Hyde\Support\Models\File
     */
    public static function make(string $path, ?string $belongsToClass = null): static
    {
        return new static($path, $belongsToClass);
    }

    /**
     * @param  string  $path  The path relative to the project root.
     * @param  string<\Hyde\Pages\Concerns\HydePage>|null  $belongsToClass
     */
    public function __construct(string $path, ?string $belongsToClass = null)
    {
        $this->path = Hyde::pathToRelative($path);
        $this->belongsTo = $belongsToClass;
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
     * @param  string<\Hyde\Pages\Concerns\HydePage>|null  $class
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

    /**
     * Check if the file belongs to a page. If a class is specified,
     * the method will check if the file belongs to that class.
     * Leave blank to check if the file belongs to any page.
     *
     * @param  string<\Hyde\Pages\Concerns\HydePage>|null  $class
     * @return bool
     */
    public function belongsToPage(?string $class = null): bool
    {
        if ($class) {
            return $this->belongsTo === $class;
        }

        return $this->isSourceFile();
    }

    public function isSourceFile(): bool
    {
        return $this->belongsTo !== null;
    }

    public function isMediaFile(): bool
    {
        return $this->belongsTo === null || str_starts_with((string) $this, '_media');
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
            'xml'  => 'application/xml',
        ];

        if (isset($lookup[$extension])) {
            return $lookup[$extension];
        }

        if (extension_loaded('fileinfo') && file_exists($this->getAbsolutePath())) {
            return mime_content_type($this->path);
        }

        return 'text/plain';
    }

    public function getExtension(): string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
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

    public function withoutDirectoryPrefix(): string
    {
        if ($this->isSourceFile()) {
            // If a model is set, use that to remove the directory, so any subdirectories within is retained
            return substr($this->__toString(), strlen($this->belongsTo::$sourceDirectory) + 1);
        }

        return basename($this->__toString());
    }
}
