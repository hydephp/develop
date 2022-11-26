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
    /** @inheritDoc */
    public function exists(string $path): bool
    {
        // TODO: Implement exists() method.
    }

    /** @inheritDoc */
    public function missing(string $path): bool
    {
        // TODO: Implement missing() method.
    }

    /** @inheritDoc */
    public function get(string $path, bool $lock = false): string
    {
        // TODO: Implement get() method.
    }

    /** @inheritDoc */
    public function sharedGet(string $path): string
    {
        // TODO: Implement sharedGet() method.
    }

    /** @inheritDoc */
    public function getRequire(string $path, array $data = []): mixed
    {
        // TODO: Implement getRequire() method.
    }

    /** @inheritDoc */
    public function requireOnce(string $path, array $data = []): mixed
    {
        // TODO: Implement requireOnce() method.
    }

    /** @inheritDoc */
    public function lines(string $path): \Illuminate\Support\LazyCollection
    {
        // TODO: Implement lines() method.
    }

    /** @inheritDoc */
    public function hash(string $path, string $algorithm = 'md5'): string
    {
        // TODO: Implement hash() method.
    }

    /** @inheritDoc */
    public function put(string $path, string $contents, bool $lock = false): bool|int
    {
        // TODO: Implement put() method.
    }

    /** @inheritDoc */
    public function replace(string $path, string $content): void
    {
        // TODO: Implement replace() method.
    }

    /** @inheritDoc */
    public function replaceInFile(array|string $search, array|string $replace, string $path): void
    {
        // TODO: Implement replaceInFile() method.
    }

    /** @inheritDoc */
    public function prepend(string $path, string $data): int
    {
        // TODO: Implement prepend() method.
    }

    /** @inheritDoc */
    public function append(string $path, string $data): int
    {
        // TODO: Implement append() method.
    }

    /** @inheritDoc */
    public function chmod(string $path, int $mode = null): mixed
    {
        // TODO: Implement chmod() method.
    }

    /** @inheritDoc */
    public function delete(array|string $paths): bool
    {
        // TODO: Implement delete() method.
    }

    /** @inheritDoc */
    public function move(string $path, string $target): bool
    {
        // TODO: Implement move() method.
    }

    /** @inheritDoc */
    public function copy(string $path, string $target): bool
    {
        // TODO: Implement copy() method.
    }

    /** @inheritDoc */
    public function link(string $target, string $link): void
    {
        // TODO: Implement link() method.
    }

    /** @inheritDoc */
    public function relativeLink(string $target, string $link): void
    {
        // TODO: Implement relativeLink() method.
    }

    /** @inheritDoc */
    public function name(string $path): string
    {
        // TODO: Implement name() method.
    }

    /** @inheritDoc */
    public function basename(string $path): string
    {
        // TODO: Implement basename() method.
    }

    /** @inheritDoc */
    public function dirname(string $path): string
    {
        // TODO: Implement dirname() method.
    }

    /** @inheritDoc */
    public function extension(string $path): string
    {
        // TODO: Implement extension() method.
    }

    /** @inheritDoc */
    public function guessExtension(string $path): ?string
    {
        // TODO: Implement guessExtension() method.
    }

    /** @inheritDoc */
    public function type(string $path): string
    {
        // TODO: Implement type() method.
    }

    /** @inheritDoc */
    public function mimeType(string $path): bool|string
    {
        // TODO: Implement mimeType() method.
    }

    /** @inheritDoc */
    public function size(string $path): int
    {
        // TODO: Implement size() method.
    }

    /** @inheritDoc */
    public function lastModified(string $path): int
    {
        // TODO: Implement lastModified() method.
    }

    /** @inheritDoc */
    public function isDirectory(string $directory): bool
    {
        // TODO: Implement isDirectory() method.
    }

    /** @inheritDoc */
    public function isEmptyDirectory(string $directory, bool $ignoreDotFiles = false): bool
    {
        // TODO: Implement isEmptyDirectory() method.
    }

    /** @inheritDoc */
    public function isReadable(string $path): bool
    {
        // TODO: Implement isReadable() method.
    }

    /** @inheritDoc */
    public function isWritable(string $path): bool
    {
        // TODO: Implement isWritable() method.
    }

    /** @inheritDoc */
    public function hasSameHash(string $firstFile, string $secondFile): bool
    {
        // TODO: Implement hasSameHash() method.
    }

    /** @inheritDoc */
    public function isFile(string $file): bool
    {
        // TODO: Implement isFile() method.
    }

    /** @inheritDoc */
    public function glob(string $pattern, int $flags = 0): array
    {
        // TODO: Implement glob() method.
    }

    /** @inheritDoc */
    public function files(string $directory, bool $hidden = false): array
    {
        // TODO: Implement files() method.
    }

    /** @inheritDoc */
    public function allFiles(string $directory, bool $hidden = false): array
    {
        // TODO: Implement allFiles() method.
    }

    /** @inheritDoc*/
    public function directories(string $directory): array
    {
        // TODO: Implement directories() method.
    }

    /** @inheritDoc */
    public function ensureDirectoryExists(string $path, int $mode = 0755, bool $recursive = true): void
    {
        // TODO: Implement ensureDirectoryExists() method.
    }

    /** @inheritDoc */
    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        // TODO: Implement makeDirectory() method.
    }

    /** @inheritDoc */
    public function moveDirectory(string $from, string $to, bool $overwrite = false): bool
    {
        // TODO: Implement moveDirectory() method.
    }

    /** @inheritDoc */
    public function copyDirectory(string $directory, string $destination, ?int $options = null): bool
    {
        // TODO: Implement copyDirectory() method.
    }

    /** @inheritDoc */
    public function deleteDirectory(string $directory, bool $preserve = false): bool
    {
        // TODO: Implement deleteDirectory() method.
    }

    /** @inheritDoc */
    public function deleteDirectories(string $directory): bool
    {
        // TODO: Implement deleteDirectories() method.
    }

    /** @inheritDoc */
    public function cleanDirectory(string $directory): bool
    {
        // TODO: Implement cleanDirectory() method.
    }
}
