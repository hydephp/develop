<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Hyde;
use Hyde\Support\Contracts\FilesystemContract;
use Illuminate\Support\Facades\File;

/**
 * Proxies the Laravel File facade with extra features and helpers tailored for HydePHP.
 *
 * @see \Hyde\Foundation\Filesystem
 * @see \Illuminate\Filesystem\Filesystem
 * @see \Hyde\Framework\Testing\Feature\FilesystemFacadeTest
 */
class Filesystem implements FilesystemContract
{
    /** @inheritDoc */
    public static function exists(string $path): bool
    {
        return self::filesystem()->exists(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function missing(string $path): bool
    {
        return self::filesystem()->missing(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function get(string $path, bool $lock = false): string
    {
        return self::filesystem()->get(Hyde::path($path), $lock);
    }

    /** @inheritDoc */
    public static function sharedGet(string $path): string
    {
        return self::filesystem()->sharedGet(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function getRequire(string $path, array $data = []): mixed
    {
        return self::filesystem()->getRequire(Hyde::path($path), $data);
    }

    /** @inheritDoc */
    public static function requireOnce(string $path, array $data = []): mixed
    {
        return self::filesystem()->requireOnce(Hyde::path($path), $data);
    }

    /** @inheritDoc */
    public static function lines(string $path): \Illuminate\Support\LazyCollection
    {
        return self::filesystem()->lines(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function hash(string $path, string $algorithm = 'md5'): string
    {
        return self::filesystem()->hash(Hyde::path($path), $algorithm);
    }

    /** @inheritDoc */
    public static function put(string $path, string $contents, bool $lock = false): bool|int
    {
        return self::filesystem()->put(Hyde::path($path), $contents, $lock);
    }

    /** @inheritDoc */
    public static function replace(string $path, string $content): void
    {
        self::filesystem()->replace(Hyde::path($path), $content);
    }

    /** @inheritDoc */
    public static function replaceInFile(array|string $search, array|string $replace, string $path): void
    {
        self::filesystem()->replaceInFile($search, $replace, Hyde::path($path));
    }

    /** @inheritDoc */
    public static function prepend(string $path, string $data): int
    {
        // TODO: Implement prepend() method.
    }

    /** @inheritDoc */
    public static function append(string $path, string $data): int
    {
        // TODO: Implement append() method.
    }

    /** @inheritDoc */
    public static function chmod(string $path, int $mode = null): mixed
    {
        // TODO: Implement chmod() method.
    }

    /** @inheritDoc */
    public static function delete(array|string $paths): bool
    {
        // TODO: Implement delete() method.
    }

    /** @inheritDoc */
    public static function move(string $path, string $target): bool
    {
        // TODO: Implement move() method.
    }

    /** @inheritDoc */
    public static function copy(string $path, string $target): bool
    {
        // TODO: Implement copy() method.
    }

    /** @inheritDoc */
    public static function link(string $target, string $link): void
    {
        // TODO: Implement link() method.
    }

    /** @inheritDoc */
    public static function relativeLink(string $target, string $link): void
    {
        // TODO: Implement relativeLink() method.
    }

    /** @inheritDoc */
    public static function name(string $path): string
    {
        // TODO: Implement name() method.
    }

    /** @inheritDoc */
    public static function basename(string $path): string
    {
        // TODO: Implement basename() method.
    }

    /** @inheritDoc */
    public static function dirname(string $path): string
    {
        // TODO: Implement dirname() method.
    }

    /** @inheritDoc */
    public static function extension(string $path): string
    {
        // TODO: Implement extension() method.
    }

    /** @inheritDoc */
    public static function guessExtension(string $path): ?string
    {
        // TODO: Implement guessExtension() method.
    }

    /** @inheritDoc */
    public static function type(string $path): string
    {
        // TODO: Implement type() method.
    }

    /** @inheritDoc */
    public static function mimeType(string $path): bool|string
    {
        // TODO: Implement mimeType() method.
    }

    /** @inheritDoc */
    public static function size(string $path): int
    {
        // TODO: Implement size() method.
    }

    /** @inheritDoc */
    public static function lastModified(string $path): int
    {
        // TODO: Implement lastModified() method.
    }

    /** @inheritDoc */
    public static function isDirectory(string $directory): bool
    {
        // TODO: Implement isDirectory() method.
    }

    /** @inheritDoc */
    public static function isEmptyDirectory(string $directory, bool $ignoreDotFiles = false): bool
    {
        // TODO: Implement isEmptyDirectory() method.
    }

    /** @inheritDoc */
    public static function isReadable(string $path): bool
    {
        // TODO: Implement isReadable() method.
    }

    /** @inheritDoc */
    public static function isWritable(string $path): bool
    {
        // TODO: Implement isWritable() method.
    }

    /** @inheritDoc */
    public static function hasSameHash(string $firstFile, string $secondFile): bool
    {
        // TODO: Implement hasSameHash() method.
    }

    /** @inheritDoc */
    public static function isFile(string $file): bool
    {
        // TODO: Implement isFile() method.
    }

    /** @inheritDoc */
    public static function glob(string $pattern, int $flags = 0): array
    {
        // TODO: Implement glob() method.
    }

    /** @inheritDoc */
    public static function files(string $directory, bool $hidden = false): array
    {
        // TODO: Implement files() method.
    }

    /** @inheritDoc */
    public static function allFiles(string $directory, bool $hidden = false): array
    {
        // TODO: Implement allFiles() method.
    }

    /** @inheritDoc*/
    public static function directories(string $directory): array
    {
        // TODO: Implement directories() method.
    }

    /** @inheritDoc */
    public static function ensureDirectoryExists(string $path, int $mode = 0755, bool $recursive = true): void
    {
        // TODO: Implement ensureDirectoryExists() method.
    }

    /** @inheritDoc */
    public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        // TODO: Implement makeDirectory() method.
    }

    /** @inheritDoc */
    public static function moveDirectory(string $from, string $to, bool $overwrite = false): bool
    {
        // TODO: Implement moveDirectory() method.
    }

    /** @inheritDoc */
    public static function copyDirectory(string $directory, string $destination, ?int $options = null): bool
    {
        // TODO: Implement copyDirectory() method.
    }

    /** @inheritDoc */
    public static function deleteDirectory(string $directory, bool $preserve = false): bool
    {
        // TODO: Implement deleteDirectory() method.
    }

    /** @inheritDoc */
    public static function deleteDirectories(string $directory): bool
    {
        // TODO: Implement deleteDirectories() method.
    }

    /** @inheritDoc */
    public static function cleanDirectory(string $directory): bool
    {
        // TODO: Implement cleanDirectory() method.
    }

    protected static function filesystem(): \Illuminate\Filesystem\Filesystem
    {
        return File::getFacadeRoot();
    }
}
