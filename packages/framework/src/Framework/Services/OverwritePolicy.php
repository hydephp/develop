<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Enums\OverwriteAction;
use Hyde\Facades\Filesystem;
use RuntimeException;

use function Hyde\unixsum_file;

/**
 * The comparison is EOL-agnostic (via {@see \Hyde\unixsum_file()}) so that files differing only by
 * their line endings (for example after a CRLF checkout) are treated as unchanged rather than modified.
 */
class OverwritePolicy
{
    public static function decide(string $source, string $destination): OverwriteAction
    {
        if (! Filesystem::exists($source)) {
            throw new RuntimeException("Cannot publish: source file [$source] does not exist.");
        }

        if (! Filesystem::isFile($source)) {
            throw new RuntimeException("Cannot publish: source [$source] is not a file.");
        }

        if (! Filesystem::exists($destination)) {
            return OverwriteAction::Copy;
        }

        if (Filesystem::isDirectory($destination)) {
            throw new RuntimeException("Cannot publish: destination [$destination] is a directory.");
        }

        if (static::filesMatch($source, $destination)) {
            return OverwriteAction::Skip;
        }

        return OverwriteAction::Blocked;
    }

    /** Compare two files EOL-agnostically so line-ending differences do not count as modifications. */
    protected static function filesMatch(string $source, string $destination): bool
    {
        return unixsum_file($source) === unixsum_file($destination);
    }
}
