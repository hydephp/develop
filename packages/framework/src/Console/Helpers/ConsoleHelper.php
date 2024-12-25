<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Closure;
use Illuminate\Support\Collection;

use function Laravel\Prompts\multiselect;

/**
 * @internal This class contains internal helpers for interacting with the console, and for easier testing.
 *
 * @codeCoverageIgnore This class provides internal testing helpers and does not need to be tested.
 */
class ConsoleHelper
{
    protected static array $mocks = [];

    public static function clearMocks(): void
    {
        static::$mocks = [];
    }

    public static function usesWindowsOs()
    {
        if (isset(static::$mocks['usesWindowsOs'])) {
            return static::$mocks['usesWindowsOs'];
        }

        return windows_os();
    }

    public static function mockWindowsOs(bool $isWindows = true): void
    {
        static::$mocks['usesWindowsOs'] = $isWindows;
    }

    /* @return array<int|string> */
    public static function multiselect(string $label, array|Collection $options, array|Collection $default = [], int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.', ?Closure $transform = null): array
    {
        if (isset(static::$mocks['multiselect'])) {
            $returns = static::$mocks['multiselect'];
            $assertionCallback = static::$mocks['multiselectAssertion'] ?? null;

            if ($assertionCallback !== null) {
                $assertionCallback(...func_get_args());
            }

            return $returns;
        }

        return multiselect(...func_get_args());
    }

    public static function mockMultiselect(array $returns, ?Closure $assertionCallback = null): void
    {
        assert(! isset(static::$mocks['multiselect']), 'Cannot mock multiselect twice.');

        static::$mocks['multiselect'] = $returns;
        static::$mocks['multiselectAssertion'] = $assertionCallback;
    }
}
