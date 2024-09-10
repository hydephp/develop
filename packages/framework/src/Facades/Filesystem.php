<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Concerns\Internal\ForwardsIlluminateFilesystem;
use Illuminate\Support\Collection;

use function app;

/**
 * Proxies the Laravel File facade with extra features and helpers tailored for HydePHP.
 *
 * For maximum compatability and interoperability, all path references in HydePHP are relative to the root of the project.
 * The helpers here will then prepend the project root to the path before actually interacting with the filesystem.
 *
 * @see \Hyde\Foundation\Kernel\Filesystem
 * @see \Illuminate\Filesystem\Filesystem
 */
class Filesystem
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
     * @return \Illuminate\Support\Collection<int, string>
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
     * Unlink a file in the project's directory, but only if it exists.
     *
     * @param  string  $path
     * @return bool True if the file was unlinked, false if it did not exist or failed to unlink.
     */
    public static function unlinkIfExists(string $path): bool
    {
        return self::kernel()->filesystem()->unlinkIfExists($path);
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
        return self::get(...func_get_args());
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
        return self::put(...func_get_args());
    }

    protected static function filesystem(): \Illuminate\Filesystem\Filesystem
    {
        return app(\Illuminate\Filesystem\Filesystem::class);
    }

    protected static function kernel(): HydeKernel
    {
        return HydeKernel::getInstance();
    }
}
