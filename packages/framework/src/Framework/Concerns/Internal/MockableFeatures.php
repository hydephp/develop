<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Enums\Feature;
use Illuminate\Support\Str;

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

    public static function mock(string $feature, bool $enabled): void
    {
        static::$mockedInstances[Str::studly($feature)] = $enabled;
    }

    public static function resolveMockedInstance(Feature|string $feature): ?bool
    {
        if ($feature instanceof Feature) {
            $feature = $feature->name;
        }

        return static::$mockedInstances[Str::studly($feature)] ?? null;
    }

    public static function clearMockedInstances(): void
    {
        static::$mockedInstances = [];
    }
}
