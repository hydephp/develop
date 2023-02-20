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
    private static bool $mocksActive = false;

    /** Determine if the application is running in a Phar archive. */
    public static function active(): bool
    {
        return self::$mocksActive || Phar::running() !== '';
    }

    /** @internal Mock the Phar active state. */
    public static function mockActive(bool $active): void
    {
        self::$mocksActive = $active;
    }
}
