<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Illuminate\Foundation\Console\VendorPublishCommand as BaseCommand;
use Illuminate\Support\ServiceProvider;
use NunoMaduro\LaravelConsoleSummary\LaravelConsoleSummaryServiceProvider;
use function base_path;
use function ltrim;
use function realpath;
use function sprintf;
use function str_replace;

/**
 * Publish any publishable assets from vendor packages.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\VendorPublishCommandTest
 */
class VendorPublishCommand extends BaseCommand
{
    /**
     * Our child method filters the options available to the parent method.
     */
    public function handle(): void
    {
        $originalPublishers = ServiceProvider::$publishes;
        $originalGroups = ServiceProvider::$publishGroups;

        // This provider's publisher is not needed as it's covered by Laravel Zero
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

    /**
     * Write a status message to the console.
     *
     * @param  string  $from
     * @param  string  $to
     * @param  string  $type
     * @return void
     */
    protected function status($from, $to, $type): void
    {
        $from = ltrim(Hyde::pathToRelative(str_replace(base_path(), '', realpath($from))), '/\\');

        $to = ltrim(Hyde::pathToRelative(str_replace(base_path(), '', realpath($to))), '/\\');

        $this->components->task(sprintf('Copying %s [%s] to [%s]', $type, $from, $to));
    }
}
