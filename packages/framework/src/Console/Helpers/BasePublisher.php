<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Facades\Filesystem;
use RuntimeException;

/**
 * @internal Shared filesystem operations for publish command helpers.
 */
abstract class BasePublisher
{
    protected function copy(string $source, string $target): void
    {
        Filesystem::ensureParentDirectoryExists($target);

        if (! Filesystem::copy($source, $target)) {
            throw new RuntimeException("Failed to copy [$source] to [$target].");
        }
    }
}
