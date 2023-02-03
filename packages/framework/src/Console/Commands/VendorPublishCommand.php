<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Illuminate\Foundation\Console\VendorPublishCommand as BaseCommand;
use Illuminate\Support\ServiceProvider;
use NunoMaduro\LaravelConsoleSummary\LaravelConsoleSummaryServiceProvider;

/**
 * Publish any publishable assets from vendor packages.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\VendorPublishCommandTest
 */
class VendorPublishCommand extends BaseCommand
{
    public function handle(): void
    {
        $originalPublishers = ServiceProvider::$publishes;
        $originalGroups = ServiceProvider::$publishGroups;

        unset(ServiceProvider::$publishes[LaravelConsoleSummaryServiceProvider::class]);

        // Rename the config group to be more helpful
        if (isset(ServiceProvider::$publishGroups['config'])) {
            ServiceProvider::$publishGroups['vendor-configs'] = ServiceProvider::$publishGroups['config'];
            unset(ServiceProvider::$publishGroups['config']);
        }

        parent::handle();

        ServiceProvider::$publishes = $originalPublishers;
        ServiceProvider::$publishGroups = $originalGroups;
    }

    protected function publishableChoices(): array
    {
        $array = parent::publishableChoices();

        $array = $this->replaceByValue($array, '<fg=gray>Tag:</> config', '<fg=gray>Tag:</> config (Vendor Configs)');

        return $this->withoutProvider($array, LaravelConsoleSummaryServiceProvider::class);
    }

    protected function withoutProvider(array $array, string $provider): array
    {
        return $this->unsetByValue($array, "<fg=gray>Provider:</> $provider");
    }

    protected function unsetByValue(array &$array, string $value): array
    {
        $key = array_search($value, $array);
        if ($key !== false) {
            unset($array[$key]);
        }
        return $array;
    }

    protected function replaceByValue(array &$array, string $value, string $newValue): array
    {
        $key = array_search($value, $array);
        if ($key !== false) {
            $array[$key] = $newValue;
        }
        return $array;
    }
}
