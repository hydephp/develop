<?php

declare(strict_types=1);

namespace Hyde\Facades;

use function array_map;
use function collect;
use Hyde\Hyde;
use Hyde\Support\Contracts\FilesystemContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use function is_array;

/**
 * Proxies the Laravel File facade with extra features and helpers tailored for HydePHP.
 *
 * @see \Hyde\Foundation\Filesystem
 * @see \Illuminate\Filesystem\Filesystem
 * @see \Hyde\Framework\Testing\Feature\FilesystemFacadeTest
 */
class Filesystem implements FilesystemContract
{
    /**
     * Format the given project path to be absolute. Already absolute paths are normalized.
     *
     * @param  string  $path
     * @return string
     */
    public static function absolutePath(string $path = ''): string
    {
        return Hyde::pathToAbsolute(self::relativePath($path));
    }

    /**
     * Remove the absolute path from the given project path so that it becomes relative.
     *
     * @param  string  $path
     * @return string
     */
    public static function relativePath(string $path): string
    {
        return Hyde::pathToRelative($path);
    }

    /**
     * A smarter glob function that will run the specified glob pattern a bit more intelligently.
     * While this method will use the absolute path when interacting with the filesystem,
     * the returned collection will only contain relative paths.
     *
     * @param  string  $pattern
     * @param  int  $flags
     * @return \Illuminate\Support\Collection<string>
     */
    public static function smartGlob(string $pattern, int $flags = 0): Collection
    {
        return collect(self::glob($pattern, $flags))
            ->map(fn (string $path): string => self::relativePath($path));
    }

    /**
     * Touch one or more files in the project's directory.
     *
     * @param  string|array  $path
     * @return bool
     */
    public static function touch(string|array $path): bool
    {
        return Hyde::filesystem()->touch($path);
    }

    /**
     * Unlink one or more files in the project's directory.
     *
     * @param  string|array  $path
     * @return bool
     */
    public static function unlink(string|array $path): bool
    {
        return Hyde::filesystem()->unlink($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @param  bool  $lock
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function getContents(string $path, bool $lock = false): string
    {
        return self::get($path, $lock);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string  $contents
     * @param  bool  $lock
     * @return int|bool
     */
    public static function putContents(string $path, string $contents, bool $lock = false): bool|int
    {
        return self::put($path, $contents, $lock);
    }

    /** @inheritDoc */
    public static function exists(string $path): bool
    {
        return self::filesystem()->exists(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function missing(string $path): bool
    {
        return self::filesystem()->missing(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function get(string $path, bool $lock = false): string
    {
        return self::filesystem()->get(self::absolutePath($path), $lock);
    }

    /** @inheritDoc */
    public static function sharedGet(string $path): string
    {
        return self::filesystem()->sharedGet(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function getRequire(string $path, array $data = []): mixed
    {
        return self::filesystem()->getRequire(self::absolutePath($path), $data);
    }

    /** @inheritDoc */
    public static function requireOnce(string $path, array $data = []): mixed
    {
        return self::filesystem()->requireOnce(self::absolutePath($path), $data);
    }

    /** @inheritDoc */
    public static function lines(string $path): \Illuminate\Support\LazyCollection
    {
        return self::filesystem()->lines(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function hash(string $path, string $algorithm = 'md5'): string
    {
        return self::filesystem()->hash(self::absolutePath($path), $algorithm);
    }

    /** @inheritDoc */
    public static function put(string $path, string $contents, bool $lock = false): bool|int
    {
        return self::filesystem()->put(self::absolutePath($path), $contents, $lock);
    }

    /** @inheritDoc */
    public static function replace(string $path, string $content): void
    {
        self::filesystem()->replace(self::absolutePath($path), $content);
    }

    /** @inheritDoc */
    public static function replaceInFile(array|string $search, array|string $replace, string $path): void
    {
        self::filesystem()->replaceInFile($search, $replace, self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function prepend(string $path, string $data): int
    {
        return self::filesystem()->prepend(self::absolutePath($path), $data);
    }

    /** @inheritDoc */
    public static function append(string $path, string $data): int
    {
        return self::filesystem()->append(self::absolutePath($path), $data);
    }

    /** @inheritDoc */
    public static function chmod(string $path, int $mode = null): mixed
    {
        return self::filesystem()->chmod(self::absolutePath($path), $mode);
    }

    /** @inheritDoc */
    public static function delete(array|string $paths): bool
    {
        return self::filesystem()->delete(self::qualifyPossiblePathArray($paths));
    }

    /** @inheritDoc */
    public static function move(string $path, string $target): bool
    {
        return self::filesystem()->move(self::absolutePath($path), self::absolutePath($target));
    }

    /** @inheritDoc */
    public static function copy(string $path, string $target): bool
    {
        return self::filesystem()->copy(self::absolutePath($path), self::absolutePath($target));
    }

    /** @inheritDoc */
    public static function link(string $target, string $link): void
    {
        self::filesystem()->link(self::absolutePath($target), self::absolutePath($link));
    }

    /** @inheritDoc */
    public static function relativeLink(string $target, string $link): void
    {
        self::filesystem()->relativeLink(self::absolutePath($target), self::absolutePath($link));
    }

    /** @inheritDoc */
    public static function name(string $path): string
    {
        return self::filesystem()->name(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function basename(string $path): string
    {
        return self::filesystem()->basename(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function dirname(string $path): string
    {
        return self::filesystem()->dirname(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function extension(string $path): string
    {
        return self::filesystem()->extension(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function guessExtension(string $path): ?string
    {
        return self::filesystem()->guessExtension(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function type(string $path): string
    {
        return self::filesystem()->type(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function mimeType(string $path): bool|string
    {
        return self::filesystem()->mimeType(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function size(string $path): int
    {
        return self::filesystem()->size(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function lastModified(string $path): int
    {
        return self::filesystem()->lastModified(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function isDirectory(string $directory): bool
    {
        return self::filesystem()->isDirectory(self::absolutePath($directory));
    }

    /** @inheritDoc */
    public static function isEmptyDirectory(string $directory, bool $ignoreDotFiles = false): bool
    {
        return self::filesystem()->isEmptyDirectory(self::absolutePath($directory), $ignoreDotFiles);
    }

    /** @inheritDoc */
    public static function isReadable(string $path): bool
    {
        return self::filesystem()->isReadable(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function isWritable(string $path): bool
    {
        return self::filesystem()->isWritable(self::absolutePath($path));
    }

    /** @inheritDoc */
    public static function hasSameHash(string $firstFile, string $secondFile): bool
    {
        return self::filesystem()->hasSameHash(self::absolutePath($firstFile), self::absolutePath($secondFile));
    }

    /** @inheritDoc */
    public static function isFile(string $file): bool
    {
        return self::filesystem()->isFile(self::absolutePath($file));
    }

    /** @inheritDoc */
    public static function glob(string $pattern, int $flags = 0): array
    {
        return self::filesystem()->glob(self::absolutePath($pattern), $flags);
    }

    /** @inheritDoc */
    public static function files(string $directory, bool $hidden = false): array
    {
        return self::filesystem()->files(self::absolutePath($directory), $hidden);
    }

    /** @inheritDoc */
    public static function allFiles(string $directory, bool $hidden = false): array
    {
        return self::filesystem()->allFiles(self::absolutePath($directory), $hidden);
    }

    /** @inheritDoc*/
    public static function directories(string $directory): array
    {
        return self::filesystem()->directories(self::absolutePath($directory));
    }

    /** @inheritDoc */
    public static function ensureDirectoryExists(string $path, int $mode = 0755, bool $recursive = true): void
    {
        self::filesystem()->ensureDirectoryExists(self::absolutePath($path), $mode, $recursive);
    }

    /** @inheritDoc */
    public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        return self::filesystem()->makeDirectory(self::absolutePath($path), $mode, $recursive, $force);
    }

    /** @inheritDoc */
    public static function moveDirectory(string $from, string $to, bool $overwrite = false): bool
    {
        return self::filesystem()->moveDirectory(self::absolutePath($from), self::absolutePath($to), $overwrite);
    }

    /** @inheritDoc */
    public static function copyDirectory(string $directory, string $destination, ?int $options = null): bool
    {
        return self::filesystem()->copyDirectory(self::absolutePath($directory), self::absolutePath($destination), $options);
    }

    /** @inheritDoc */
    public static function deleteDirectory(string $directory, bool $preserve = false): bool
    {
        return self::filesystem()->deleteDirectory(self::absolutePath($directory), $preserve);
    }

    /** @inheritDoc */
    public static function deleteDirectories(string $directory): bool
    {
        return self::filesystem()->deleteDirectories(self::absolutePath($directory));
    }

    /** @inheritDoc */
    public static function cleanDirectory(string $directory): bool
    {
        return self::filesystem()->cleanDirectory(self::absolutePath($directory));
    }

    protected static function filesystem(): \Illuminate\Filesystem\Filesystem
    {
        return File::getFacadeRoot();
    }

    protected static function qualifyPossiblePathArray(array|string $paths): array|string
    {
        if (is_array($paths)) {
            return array_map(fn ($path) => self::absolutePath($path), $paths);
        }

        return self::absolutePath($paths);
    }
}
