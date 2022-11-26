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
    public static function absolutePath(string $path = ''): string
    {
        return Hyde::path(Hyde::pathToRelative($path));
    }

    public static function relativePath(string $path): string
    {
        return Hyde::pathToRelative($path);
    }

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
        return self::filesystem()->prepend(Hyde::path($path), $data);
    }

    /** @inheritDoc */
    public static function append(string $path, string $data): int
    {
        return self::filesystem()->append(Hyde::path($path), $data);
    }

    /** @inheritDoc */
    public static function chmod(string $path, int $mode = null): mixed
    {
        return self::filesystem()->chmod(Hyde::path($path), $mode);
    }

    /** @inheritDoc */
    public static function delete(array|string $paths): bool
    {
        return self::filesystem()->delete(Hyde::path($paths));
    }

    /** @inheritDoc */
    public static function move(string $path, string $target): bool
    {
        return self::filesystem()->move(Hyde::path($path), Hyde::path($target));
    }

    /** @inheritDoc */
    public static function copy(string $path, string $target): bool
    {
        return self::filesystem()->copy(Hyde::path($path), Hyde::path($target));
    }

    /** @inheritDoc */
    public static function link(string $target, string $link): void
    {
        self::filesystem()->link(Hyde::path($target), Hyde::path($link));
    }

    /** @inheritDoc */
    public static function relativeLink(string $target, string $link): void
    {
        self::filesystem()->relativeLink(Hyde::path($target), Hyde::path($link));
    }

    /** @inheritDoc */
    public static function name(string $path): string
    {
        return self::filesystem()->name(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function basename(string $path): string
    {
        return self::filesystem()->basename(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function dirname(string $path): string
    {
        return self::filesystem()->dirname(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function extension(string $path): string
    {
        return self::filesystem()->extension(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function guessExtension(string $path): ?string
    {
        return self::filesystem()->guessExtension(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function type(string $path): string
    {
        return self::filesystem()->type(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function mimeType(string $path): bool|string
    {
        return self::filesystem()->mimeType(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function size(string $path): int
    {
        return self::filesystem()->size(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function lastModified(string $path): int
    {
        return self::filesystem()->lastModified(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function isDirectory(string $directory): bool
    {
        return self::filesystem()->isDirectory(Hyde::path($directory));
    }

    /** @inheritDoc */
    public static function isEmptyDirectory(string $directory, bool $ignoreDotFiles = false): bool
    {
        return self::filesystem()->isEmptyDirectory(Hyde::path($directory), $ignoreDotFiles);
    }

    /** @inheritDoc */
    public static function isReadable(string $path): bool
    {
        return self::filesystem()->isReadable(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function isWritable(string $path): bool
    {
        return self::filesystem()->isWritable(Hyde::path($path));
    }

    /** @inheritDoc */
    public static function hasSameHash(string $firstFile, string $secondFile): bool
    {
        return self::filesystem()->hasSameHash(Hyde::path($firstFile), Hyde::path($secondFile));
    }

    /** @inheritDoc */
    public static function isFile(string $file): bool
    {
        return self::filesystem()->isFile(Hyde::path($file));
    }

    /** @inheritDoc */
    public static function glob(string $pattern, int $flags = 0): array
    {
        return self::filesystem()->glob(Hyde::path($pattern), $flags);
    }

    /** @inheritDoc */
    public static function files(string $directory, bool $hidden = false): array
    {
        return self::filesystem()->files(Hyde::path($directory), $hidden);
    }

    /** @inheritDoc */
    public static function allFiles(string $directory, bool $hidden = false): array
    {
        return self::filesystem()->allFiles(Hyde::path($directory), $hidden);
    }

    /** @inheritDoc*/
    public static function directories(string $directory): array
    {
        return self::filesystem()->directories(Hyde::path($directory));
    }

    /** @inheritDoc */
    public static function ensureDirectoryExists(string $path, int $mode = 0755, bool $recursive = true): void
    {
        self::filesystem()->ensureDirectoryExists(Hyde::path($path), $mode, $recursive);
    }

    /** @inheritDoc */
    public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        return self::filesystem()->makeDirectory(Hyde::path($path), $mode, $recursive, $force);
    }

    /** @inheritDoc */
    public static function moveDirectory(string $from, string $to, bool $overwrite = false): bool
    {
        return self::filesystem()->moveDirectory(Hyde::path($from), Hyde::path($to), $overwrite);
    }

    /** @inheritDoc */
    public static function copyDirectory(string $directory, string $destination, ?int $options = null): bool
    {
        return self::filesystem()->copyDirectory(Hyde::path($directory), Hyde::path($destination), $options);
    }

    /** @inheritDoc */
    public static function deleteDirectory(string $directory, bool $preserve = false): bool
    {
        return self::filesystem()->deleteDirectory(Hyde::path($directory), $preserve);
    }

    /** @inheritDoc */
    public static function deleteDirectories(string $directory): bool
    {
        return self::filesystem()->deleteDirectories(Hyde::path($directory));
    }

    /** @inheritDoc */
    public static function cleanDirectory(string $directory): bool
    {
        return self::filesystem()->cleanDirectory(Hyde::path($directory));
    }

    protected static function filesystem(): \Illuminate\Filesystem\Filesystem
    {
        return File::getFacadeRoot();
    }
}
