<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Facades\Filesystem;
use Illuminate\Support\Collection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Illuminate\Support\Str;

use function Hyde\unslash;
use function Hyde\path_join;
use function Hyde\trim_slashes;
use function extension_loaded;
use function file_exists;
use function array_merge;
use function filesize;
use function pathinfo;

/**
 * File abstraction for a project media file.
 */
class MediaFile extends ProjectFile
{
    /** @var array<string> The default extensions for media types */
    final public const EXTENSIONS = ['png', 'svg', 'jpg', 'jpeg', 'gif', 'ico', 'css', 'js'];

    /** @internal Controls whether to validate the existence of the file. Turning this off may lead to unexpected behavior. */
    public static bool $validateExistence = true;

    protected int $length;
    protected string $mimeType;
    protected string $hash;

    public function __construct(string $path)
    {
        $path = $this->normalizePath($path);

        if (static::$validateExistence && Filesystem::missing($path)) {
            throw new FileNotFoundException($path);
        }

        parent::__construct($path);

        if (is_file($this->getAbsolutePath())) {
            $this->length = filesize($this->getAbsolutePath());
            $this->mimeType = $this->getMimeType();
            $this->hash = $this->getHash();
        }
    }

    /** @return \Illuminate\Support\Collection<string, \Hyde\Support\Filesystem\MediaFile> The array keys are the filenames relative to the _media/ directory */
    public static function all(): Collection
    {
        return Hyde::assets();
    }

    /** @return array<string> Array of filenames relative to the _media/ directory */
    public static function files(): array
    {
        return static::all()->keys()->all();
    }

    /**
     * Get the absolute path to the media source directory, or a file within it.
     */
    public static function sourcePath(string $path = ''): string
    {
        if (empty($path)) {
            return Hyde::path(Hyde::getMediaDirectory());
        }

        return Hyde::path(path_join(Hyde::getMediaDirectory(), unslash($path)));
    }

    /**
     * Get the absolute path to the compiled site's media directory, or a file within it.
     */
    public static function outputPath(string $path = ''): string
    {
        if (empty($path)) {
            return Hyde::sitePath(Hyde::getMediaOutputDirectory());
        }

        return Hyde::sitePath(path_join(Hyde::getMediaOutputDirectory(), unslash($path)));
    }

    /**
     * Get the path to the media file relative to the media directory.
     */
    public function getIdentifier(): string
    {
        return Str::after($this->getPath(), Hyde::getMediaDirectory().'/');
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'length' => $this->getContentLength(),
            'mimeType' => $this->getMimeType(),
            'hash' => $this->getHash(),
        ]);
    }

    public function getContentLength(): int
    {
        if (isset($this->length)) {
            return $this->length;
        }

        return filesize($this->getAbsolutePath());
    }

    public function getMimeType(): string
    {
        if (isset($this->mimeType)) {
            return $this->mimeType;
        }

        $extension = pathinfo($this->getAbsolutePath(), PATHINFO_EXTENSION);

        // See if we can find a mime type for the extension instead of
        // having to rely on a PHP extension and filesystem lookups.
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
            'xml' => 'application/xml',
        ];

        if (isset($lookup[$extension])) {
            return $lookup[$extension];
        }

        if (extension_loaded('fileinfo') && file_exists($this->getAbsolutePath())) {
            return mime_content_type($this->getAbsolutePath());
        }

        return 'text/plain';
    }

    public function getHash(): string
    {
        if (isset($this->hash)) {
            return $this->hash;
        }

        return hash_file('crc32', $this->getAbsolutePath());
    }

    /** @internal */
    public static function getCacheBustKey(string $file): string
    {
        return Config::getBool('hyde.enable_cache_busting', true) && file_exists(static::sourcePath("$file"))
            ? '?v='.static::make($file)->getHash()
            : '';
    }

    protected function normalizePath(string $path): string
    {
        $path = Hyde::pathToRelative($path);

        // Normalize paths using output directory to have source directory prefix
        if (str_starts_with($path, Hyde::getMediaOutputDirectory()) && str_starts_with(Hyde::getMediaDirectory(), '_')) {
            $path = '_'.$path;
        }

        return static::sourcePath(trim_slashes(Str::after($path, Hyde::getMediaDirectory())));
    }
}
