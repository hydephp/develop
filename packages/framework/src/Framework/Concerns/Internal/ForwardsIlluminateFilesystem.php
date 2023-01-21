<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Illuminate\Support\LazyCollection;

/**
 * Forwards calls to the Laravel File facade to the HydePHP Filesystem Facade.
 *
 * @interal This trait is not covered by the backward compatibility promise.
 *
 * @see \Hyde\Facades\Filesystem
 */
trait ForwardsIlluminateFilesystem
{
    /** @inheritDoc */
    public static function get(string $path, bool $lock = false): string
    {
        return self::filesystem()->get(self::absolutePath($path), $lock);
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
        return self::filesystem()->delete(self::kernel()->filesystem()->pathToAbsolute($paths));
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
    public static function isEmptyDirectory(string $directory, bool $ignoreDotFiles = false): bool
    {
        return self::filesystem()->isEmptyDirectory(self::absolutePath($directory), $ignoreDotFiles);
    }

    /** @inheritDoc */
    public static function hasSameHash(string $firstFile, string $secondFile): bool
    {
        return self::filesystem()->hasSameHash(self::absolutePath($firstFile), self::absolutePath($secondFile));
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

    protected static function callPathMethod(string $name, string $path): bool|string|int|null|array|LazyCollection
    {
        return self::filesystem()->$name(self::absolutePath($path));
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (count($arguments) === 1) {
            return self::callPathMethod($name, $arguments[0]);
        }
    }
}
