<?php

declare(strict_types=1);

namespace Hyde\Enums;

/**
 * The action the {@see \Hyde\Framework\Services\OverwritePolicy} decides to take
 * when a source file is about to be published to a destination path.
 *
 * @see \Hyde\Framework\Services\OverwritePolicy
 */
enum OverwriteAction: string
{
    /** The destination does not exist yet, so the source can be copied freely. */
    case Copy = 'copy';

    /** The destination already matches the source, so there is nothing to do. */
    case Skip = 'skip';

    /** The destination exists and differs from the source (user-modified), so overwriting is blocked. */
    case Blocked = 'blocked';

    /** The source or destination state is invalid, so this item should be skipped with an error. */
    case Error = 'error';
}
