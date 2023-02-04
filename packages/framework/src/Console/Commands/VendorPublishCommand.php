<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Illuminate\Foundation\Console\VendorPublishCommand as BaseCommand;
use Illuminate\Support\ServiceProvider;
use NunoMaduro\LaravelConsoleSummary\LaravelConsoleSummaryServiceProvider;
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
    protected int $exitCode = Command::SUCCESS;

    /**
     * Our child method filters the options available to the parent method.
     */
    public function handle(): int
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

        return $this->exitCode;
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

    /**
     * Publish the file to the given path.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     * @codeCoverageIgnore - This is a copy of the parent method with a few tweaks
     */
    protected function publishFile($from, $to)
    {
        if ((! $this->option('existing') && (! $this->files->exists($to) || $this->option('force')))
            || ($this->option('existing') && $this->files->exists($to))) {
            $this->createParentDirectory(dirname($to));

            $this->files->copy($from, $to);

            $this->status($from, $to, 'file');
        } else {
            if ($this->option('existing')) {
                $this->components->twoColumnDetail(sprintf(
                    'ProjectFile [%s] does not exist',
                    str_replace(base_path().'/', '', $to),
                ), '<fg=yellow;options=bold>SKIPPED</>');
                $this->setExitCode(404);
            } else {
                $this->components->twoColumnDetail(sprintf(
                    'ProjectFile [%s] already exists',
                    str_replace(base_path().'/', '', realpath($to)),
                ), '<fg=yellow;options=bold>SKIPPED</>');
                $this->setExitCode(409);
            }
        }
    }

    protected function setExitCode(int $code): void
    {
        if ($code > $this->exitCode) {
            $this->exitCode = $code;
        }
    }
}
