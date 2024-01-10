<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Illuminate\Support\Str;

use function extension_loaded;
use function file_exists;
use function array_merge;
use function array_keys;
use function filesize;
use function implode;
use function pathinfo;
use function collect;
use function is_file;
use function sprintf;
use function glob;

/**
 * File abstraction for a project media file.
 */
class MediaFile extends ProjectFile
{
    /** @var array<string> The default extensions for media types */
    final public const EXTENSIONS = ['png', 'svg', 'jpg', 'jpeg', 'gif', 'ico', 'css', 'js'];

    /** @return array<string, \Hyde\Support\Filesystem\MediaFile> The array keys are the filenames relative to the _media/ directory */
    public static function all(): array
    {
        return static::discoverMediaAssetFiles();
    }

    /** @return array<string> Array of filenames relative to the _media/ directory */
    public static function files(): array
    {
        return array_keys(static::all());
    }

    public function getIdentifier(): string
    {
        return Str::after($this->getPath(), Hyde::getMediaDirectory().'/');
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'length' => $this->getContentLength(),
            'mimeType' => $this->getMimeType(),
        ]);
    }

    public function getContentLength(): int
    {
        if (! is_file($this->getAbsolutePath())) {
            throw new FileNotFoundException($this->path);
        }

        return filesize($this->getAbsolutePath());
    }

    public function getMimeType(): string
    {
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

    protected static function discoverMediaAssetFiles(): array
    {
        return collect(static::getMediaAssetFiles())->mapWithKeys(function (string $path): array {
            $file = static::make($path);

            return [$file->getIdentifier() => $file];
        })->all();
    }

    protected static function getMediaAssetFiles(): array
    {
        return glob(Hyde::path(static::getMediaGlobPattern()), GLOB_BRACE) ?: [];
    }

    protected static function getMediaGlobPattern(): string
    {
        return sprintf(Hyde::getMediaDirectory().'/{*,**/*,**/*/*}.{%s}', implode(',',
            Config::getArray('hyde.media_extensions', self::EXTENSIONS)
        ));
    }
}
