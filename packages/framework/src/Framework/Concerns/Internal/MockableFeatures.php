<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Hyde;

use function is_array;

/**
 * Allows the Features class to be mocked.
 *
 * @internal This trait is not covered by the backward compatibility promise.
 *
 * @see \Hyde\Facades\Features
 */
trait MockableFeatures
{
    /** @param string|array<string, bool> $feature */
    public static function mock(string|array $feature, bool $enabled = true): void
    {
        $features = is_array($feature) ? $feature : [$feature => $enabled];

        foreach ($features as $feature => $enabled) {
            Hyde::features()->features[$feature] = $enabled;
        }
    }

    /** @deprecated Will not be needed after refactor */
    protected static function resolveMockedInstance(string $feature): ?bool
    {
        return Hyde::features()->features[$feature] ?? null;
    }
}
