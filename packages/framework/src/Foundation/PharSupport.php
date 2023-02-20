<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use Hyde\Hyde;
use Phar;
use BadMethodCallException;

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
    protected static array $mocks = [];

    /** @internal */
    public static function mock(string $method, bool $value): void
    {
        self::$mocks[$method] = $value;
    }

    /** @internal */
    public static function clearMocks(): void
    {
        self::$mocks = [];
    }

    public static function running(): bool
    {
        return self::$mocks['running'] ?? Phar::running() !== '';
    }

    public static function hasVendorDirectory(): bool
    {
        return self::$mocks['hasVendorDirectory'] ?? is_dir(Hyde::path('vendor'));
    }

    public static function vendorPath(string $path = '', string $package = 'framework'): string
    {
        if ($package !== 'framework') {
            throw new BadMethodCallException('Cannot use vendorPath() outside of the framework package when running from a Phar archive.');
        }

        return unslash(dirname(__DIR__, 2).'/'.unslash($path));
    }
}
