<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Enums\OverwriteAction;
use Hyde\Facades\Filesystem;

use function Hyde\unixsum_file;

/**
 * The shared, pure decision logic for whether a source file may be published to a destination path.
 *
 * Given a source and a destination, it decides between three outcomes without performing any I/O
 * beyond reading the two files, and without any knowledge of what is being published (views, pages, etc.):
 *
 *   - {@see OverwriteAction::Copy}    The destination is missing.
 *   - {@see OverwriteAction::Skip}    The destination is unchanged from the source.
 *   - {@see OverwriteAction::Blocked} The destination exists and was modified by the user.
 *
 * The comparison is EOL-agnostic (via {@see \Hyde\unixsum_file()}) so that files differing only by
 * their line endings (for example after a CRLF checkout) are treated as unchanged rather than modified.
 * There is no historical-checksum manifest; the destination is only ever compared to the current source.
 */
class OverwritePolicy
{
    public static function decide(string $source, string $destination): OverwriteAction
    {
        if (! Filesystem::exists($destination)) {
            return OverwriteAction::Copy;
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
