<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

/**
 * Allows the Features class to be mocked.
 *
 * @internal This trait is not covered by the backward compatibility promise.
 *
 * @see \Hyde\Facades\Features
 */
trait MockableFeatures
{
    protected static array $mockedInstances = [];

    public static function mock(string|array $feature, ?bool $enabled = null): void
    {
        if (is_array($feature)) {
            foreach ($feature as $key => $value) {
                static::mock($key, $value);
            }

            return;
        }

        static::$mockedInstances[$feature] = $enabled;
    }

    public static function resolveMockedInstance(string $feature): ?bool
    {
        return static::$mockedInstances[$feature] ?? null;
    }

    public static function clearMockedInstances(): void
    {
        static::$mockedInstances = [];
    }
}
