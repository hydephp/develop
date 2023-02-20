<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use Phar;

/**
 * Provides experimental support for running the HydeCLI in a standalone Phar archive.
 *
 * @experimental
 * @internal
 *
 * @see \Hyde\Framework\Testing\Feature\PharSupportTest
 */
class PharSupport
{
    /** Determine if the application is running in a Phar archive. */
    public static function active(): bool
    {
        return Phar::running() !== '';
    }
}
