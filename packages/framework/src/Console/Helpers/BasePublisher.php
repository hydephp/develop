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
    protected int $policyErrors = 0;

    protected function reportPolicyError(PublisherConsole $console, string $source, string $target): void
    {
        $this->policyErrors++;

        if (! Filesystem::exists($source)) {
            $console->error("Skipped [$target]: source file [$source] does not exist.");

            return;
        }

        if (! Filesystem::isFile($source)) {
            $console->error("Skipped [$target]: source [$source] is not a file.");

            return;
        }

        if (Filesystem::isDirectory($target)) {
            $console->error("Skipped [$target]: destination is a directory.");

            return;
        }

        $console->error("Skipped [$target]: source or destination is invalid.");
    }

    protected function hasPolicyErrors(): bool
    {
        return $this->policyErrors > 0;
    }

    protected function copy(string $source, string $target): void
    {
        Filesystem::ensureParentDirectoryExists($target);

        if (! Filesystem::copy($source, $target)) {
            throw new RuntimeException("Failed to copy [$source] to [$target].");
        }
    }
}
