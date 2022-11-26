<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Support\Contracts\FilesystemContract;

/**
 * Proxies the Laravel File facade with extra features and helpers tailored for HydePHP.
 *
 * @see \Hyde\Foundation\Filesystem
 * @see \Illuminate\Filesystem\Filesystem
 */
class Filesystem implements FilesystemContract
{
    public function exists(string $path): bool
    {
        // TODO: Implement exists() method.
    }

    public function missing(string $path): bool
    {
        // TODO: Implement missing() method.
    }

    public function get(string $path, bool $lock = false): string
    {
        // TODO: Implement get() method.
    }

    public function sharedGet(string $path): string
    {
        // TODO: Implement sharedGet() method.
    }

    public function getRequire(string $path, array $data = []): mixed
    {
        // TODO: Implement getRequire() method.
    }

    public function requireOnce(string $path, array $data = []): mixed
    {
        // TODO: Implement requireOnce() method.
    }

    public function lines(string $path): \Illuminate\Support\LazyCollection
    {
        // TODO: Implement lines() method.
    }

    public function hash(string $path, string $algorithm = 'md5'): string
    {
        // TODO: Implement hash() method.
    }

    public function put(string $path, string $contents, bool $lock = false): bool|int
    {
        // TODO: Implement put() method.
    }

    public function replace(string $path, string $content): void
    {
        // TODO: Implement replace() method.
    }

    public function replaceInFile(array|string $search, array|string $replace, string $path): void
    {
        // TODO: Implement replaceInFile() method.
    }

    public function prepend(string $path, string $data): int
    {
        // TODO: Implement prepend() method.
    }

    public function append(string $path, string $data): int
    {
        // TODO: Implement append() method.
    }

    public function chmod(string $path, int $mode = null): mixed
    {
        // TODO: Implement chmod() method.
    }

    public function delete(array|string $paths): bool
    {
        // TODO: Implement delete() method.
    }

    public function move(string $path, string $target): bool
    {
        // TODO: Implement move() method.
    }

    public function copy(string $path, string $target): bool
    {
        // TODO: Implement copy() method.
    }

    public function link(string $target, string $link): void
    {
        // TODO: Implement link() method.
    }

    public function relativeLink(string $target, string $link): void
    {
        // TODO: Implement relativeLink() method.
    }

    public function name(string $path): string
    {
        // TODO: Implement name() method.
    }

    public function basename(string $path): string
    {
        // TODO: Implement basename() method.
    }

    public function dirname(string $path): string
    {
        // TODO: Implement dirname() method.
    }

    public function extension(string $path): string
    {
        // TODO: Implement extension() method.
    }

    public function guessExtension(string $path): ?string
    {
        // TODO: Implement guessExtension() method.
    }

    public function type(string $path): string
    {
        // TODO: Implement type() method.
    }

    public function mimeType(string $path): bool|string
    {
        // TODO: Implement mimeType() method.
    }

    public function size(string $path): int
    {
        // TODO: Implement size() method.
    }

    public function lastModified(string $path): int
    {
        // TODO: Implement lastModified() method.
    }

    public function isDirectory(string $directory): bool
    {
        // TODO: Implement isDirectory() method.
    }

    public function isEmptyDirectory(string $directory, bool $ignoreDotFiles = false): bool
    {
        // TODO: Implement isEmptyDirectory() method.
    }

    public function isReadable(string $path): bool
    {
        // TODO: Implement isReadable() method.
    }

    public function isWritable(string $path): bool
    {
        // TODO: Implement isWritable() method.
    }

    public function hasSameHash(string $firstFile, string $secondFile): bool
    {
        // TODO: Implement hasSameHash() method.
    }

    public function isFile(string $file): bool
    {
        // TODO: Implement isFile() method.
    }

    public function glob(string $pattern, int $flags = 0): array
    {
        // TODO: Implement glob() method.
    }

    public function files(string $directory, bool $hidden = false): array
    {
        // TODO: Implement files() method.
    }

    public function allFiles(string $directory, bool $hidden = false): array
    {
        // TODO: Implement allFiles() method.
    }

    public function directories(string $directory): array
    {
        // TODO: Implement directories() method.
    }

    public function ensureDirectoryExists(string $path, int $mode = 0755, bool $recursive = true): void
    {
        // TODO: Implement ensureDirectoryExists() method.
    }

    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        // TODO: Implement makeDirectory() method.
    }

    public function moveDirectory(string $from, string $to, bool $overwrite = false): bool
    {
        // TODO: Implement moveDirectory() method.
    }

    public function copyDirectory(string $directory, string $destination, ?int $options = null): bool
    {
        // TODO: Implement copyDirectory() method.
    }

    public function deleteDirectory(string $directory, bool $preserve = false): bool
    {
        // TODO: Implement deleteDirectory() method.
    }

    public function deleteDirectories(string $directory): bool
    {
        // TODO: Implement deleteDirectories() method.
    }

    public function cleanDirectory(string $directory): bool
    {
        // TODO: Implement cleanDirectory() method.
    }
}
