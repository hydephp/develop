<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Enums\OverwriteAction;
use Hyde\Facades\Filesystem;

use function Hyde\unixsum_file;

/**
 * The comparison is EOL-agnostic (via {@see \Hyde\unixsum_file()}) so that files differing only by
 * their line endings (for example after a CRLF checkout) are treated as unchanged rather than modified.
 */
class OverwritePolicy
{
    public static function decide(string $source, string $destination): OverwriteAction
    {
        if (! Filesystem::exists($source) || ! Filesystem::isFile($source)) {
            return OverwriteAction::Error;
        }

        if (! Filesystem::exists($destination)) {
            return OverwriteAction::Copy;
        }

        if (Filesystem::isDirectory($destination)) {
            return OverwriteAction::Error;
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
