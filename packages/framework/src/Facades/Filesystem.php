<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Concerns\Internal\ForwardsIlluminateFilesystem;
use Hyde\Support\Contracts\FilesystemContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Proxies the Laravel File facade with extra features and helpers tailored for HydePHP.
 *
 * For maximum compatability and interoperability, all path references in HydePHP are relative to the root of the project.
 * The helpers here will then prepend the project root to the path before actually interacting with the filesystem.
 *
 * @see \Hyde\Foundation\Filesystem
 * @see \Illuminate\Filesystem\Filesystem
 * @see \Hyde\Framework\Testing\Feature\FilesystemFacadeTest
 */
class Filesystem implements FilesystemContract
{
    use ForwardsIlluminateFilesystem;

    /**
     * Get the base path of the HydePHP project.
     *
     * @return string
     */
    public static function basePath(): string
    {
        return self::kernel()->path();
    }

    /**
     * Format the given project path to be absolute. Already absolute paths are normalized.
     *
     * @param  string  $path
     * @return string
     */
    public static function absolutePath(string $path): string
    {
        return self::kernel()->pathToAbsolute(self::relativePath($path));
    }

    /**
     * Remove the absolute path from the given project path so that it becomes relative.
     *
     * @param  string  $path
     * @return string
     */
    public static function relativePath(string $path): string
    {
        return self::kernel()->pathToRelative($path);
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
        return self::kernel()->filesystem()->smartGlob($pattern, $flags);
    }

    /**
     * Touch one or more files in the project's directory.
     *
     * @param  string|array  $path
     * @return bool
     */
    public static function touch(string|array $path): bool
    {
        return self::kernel()->filesystem()->touch($path);
    }

    /**
     * Unlink one or more files in the project's directory.
     *
     * @param  string|array  $path
     * @return bool
     */
    public static function unlink(string|array $path): bool
    {
        return self::kernel()->filesystem()->unlink($path);
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

    protected static function qualifyPossiblePathArray(array|string $paths): array|string
    {
        return self::kernel()->filesystem()->qualifyPossiblePathArray($paths);
    }

    protected static function filesystem(): \Illuminate\Filesystem\Filesystem
    {
        return File::getFacadeRoot();
    }

    protected static function kernel(): HydeKernel
    {
        return HydeKernel::getInstance();
    }
}
