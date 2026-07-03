<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Hyde\Facades\Config;

use function explode;
use function is_numeric;
use function str_contains;
use function strtolower;

/**
 * Adds support for the repeatable `--config=key=value` option, letting a single
 * command invocation temporarily override a Hyde config value before it runs.
 */
trait HasConfigOverrides
{
    /** Check that all the provided `--config` overrides are in the expected `key=value` format. */
    protected function validateConfigOverrides(): bool
    {
        /** @var array<string> $overrides */
        $overrides = $this->option('config');

        foreach ($overrides as $override) {
            if (! str_contains($override, '=')) {
                $this->error("Invalid --config value [$override]. Expected format: key=value");

                return false;
            }
        }

        return true;
    }

    /** Apply the provided `--config` overrides to the config repository. Call after {@see validateConfigOverrides()}. */
    protected function applyConfigOverrides(): void
    {
        /** @var array<string> $overrides */
        $overrides = $this->option('config');

        foreach ($overrides as $override) {
            [$key, $value] = explode('=', $override, 2);

            Config::set([$key => $this->parseConfigOverrideValue($value)]);
        }
    }

    protected function parseConfigOverrideValue(string $value): string|int|float|bool|null
    {
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => is_numeric($value) ? $value + 0 : $value,
        };
    }
}
