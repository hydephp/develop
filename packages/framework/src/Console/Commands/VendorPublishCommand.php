<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Illuminate\Foundation\Console\VendorPublishCommand as BaseCommand;

/**
 * Publish any publishable assets from vendor packages.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\VendorPublishCommandTest
 */
class VendorPublishCommand extends BaseCommand
{
    protected function publishableChoices(): array
    {
        $array = parent::publishableChoices();
        return $this->withoutProvider($array, 'NunoMaduro\LaravelConsoleSummary\LaravelConsoleSummaryServiceProvider');
    }

    protected function withoutProvider(array $array, string $provider): array
    {
        $provider = "<fg=gray>Provider:</> $provider";
        unset($array[array_search($provider, $array)]);
        return $array;
    }
}
