<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Laravel\Prompts\Prompt;

/**
 * @internal This class contains internal helpers for interacting with the console, and for easier testing.
 *
 * @codeCoverageIgnore This class provides internal testing helpers and does not need to be tested.
 */
class ConsoleHelper
{
    /** Allows for mocking the Windows OS check. Remember to clear the mock after the test. */
    protected static ?bool $enableLaravelPrompts = null;

    public static function clearMocks(): void
    {
        self::$enableLaravelPrompts = null;
    }

    public static function mockWindowsOs(bool $isWindowsOs): void
    {
        self::$enableLaravelPrompts = ! $isWindowsOs;
    }

    public static function canUseLaravelPrompts(): bool
    {
        if (self::$enableLaravelPrompts !== null) {
            return self::$enableLaravelPrompts;
        }

        return windows_os() === false && Prompt::shouldFallback() === false;
    }
}
