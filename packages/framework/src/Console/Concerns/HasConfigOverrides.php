<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Hyde\Facades\Config;
use Hyde\Support\ConfigOverrideValueParser;

use function explode;
use function str_contains;

/**
 * Adds support for the repeatable `--config=key=value` option, letting a single
 * command invocation temporarily override a Hyde config value before it runs.
 */
trait HasConfigOverrides
{
    /** Check that all the provided `--config` overrides are in the expected `key=value` format. */
    protected function validateConfigOverrides(): bool
    {
        foreach ($this->getConfigOverrideOptions() as $override) {
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
        foreach ($this->getConfigOverrideOptions() as $override) {
            [$key, $value] = explode('=', $override, 2);

            Config::set([$key => ConfigOverrideValueParser::parse($value)]);
        }
    }

    /** @return array<string> */
    protected function getConfigOverrideOptions(): array
    {
        return (array) ($this->option('config') ?? []);
    }
}
