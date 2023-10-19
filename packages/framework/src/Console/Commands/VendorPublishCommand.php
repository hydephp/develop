<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Illuminate\Foundation\Console\VendorPublishCommand as BaseCommand;
use Illuminate\Support\ServiceProvider;
use NunoMaduro\LaravelConsoleSummary\LaravelConsoleSummaryServiceProvider;

use function ltrim;
use function realpath;
use function sprintf;
use function str_replace;
use function Laravel\Prompts\select;

/**
 * Publish any publishable assets from vendor packages.
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
            ServiceProvider::$publishGroups['vendor-configs'] = (array) ServiceProvider::$publishGroups['config'];
            unset(ServiceProvider::$publishGroups['config']);
        }

        parent::handle();

        ServiceProvider::$publishes = $originalPublishers;
        ServiceProvider::$publishGroups = $originalGroups;
    }

    /**
     * Our child method only uses the select function, instead of the search one.
     */
    protected function promptForProviderOrTag(): void
    {
        $choices = $this->publishableChoices();

        $choice = select(
            "Which provider or tag's files would you like to publish?",
            $choices,
            scroll: 15,
        );

        if ($choice == $choices[0]) {
            return;
        }

        $this->parseChoice($choice);
    }

    /**
     * Write a status message to the console.
     *
     * @param  string  $from
     * @param  string  $to
     * @param  string  $type
     */
    protected function status($from, $to, $type): void
    {
        $this->components->task(sprintf('Copying %s [%s] to [%s]', $type,
            $this->normalizePath($from),
            $this->normalizePath($to)
        ));
    }

    protected function normalizePath(string $path): string
    {
        return ltrim(str_replace('\\', '/', Hyde::pathToRelative(realpath($path))), '/\\');
    }
}
