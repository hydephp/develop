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
    protected array $mocks = [];

    public static function mock(string|array $feature, ?bool $enabled = null): void
    {
        if (is_array($feature)) {
            foreach ($feature as $key => $value) {
                static::mock($key, $value);
            }

            return;
        }

        $instance = Hyde::features();
        $instance->features[$feature] = $enabled;
    }

    /** @deprecated Will not be needed after refactor */
    protected static function resolveMockedInstance(string $feature): ?bool
    {
        $instance = Hyde::features();

        return $instance->features[$feature] ?? null;
    }
}
