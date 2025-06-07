<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Composer\InstalledVersions;
use Hyde\Foundation\PharSupport;
use LaravelZero\Framework\Commands\Command;

use function str_replace;
use function realpath;
use function app;
use function get_included_files;

/**
 * @deprecated Use 'about' command instead.
 * Print debug information.
 */
class DebugCommand extends Command
{
    /** @var string */
    protected $signature = 'debug';

    /** @var string */
    protected $description = 'Print debug information';

    public function __construct()
    {
        parent::__construct();

        if (Config::getString('app.env', 'production') !== 'development') {
            $this->setHidden();
        }
    }

    public function handle(): int
    {
        $this->info('HydePHP Debug Screen');
        $this->newLine();

        $this->comment('Hyde Version: '.((InstalledVersions::isInstalled('hyde/hyde') ? InstalledVersions::getPrettyVersion('hyde/hyde') : null) ?: 'unreleased'));
        $this->comment('Framework Version: '.(InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased'));
        $this->newLine();

        $this->comment('App Env: '.(string) app('env'));
        $this->newLine();

        if ($this->output->isVerbose()) {
            $this->printVerbosePathInformation();
        } else {
            $this->comment('Project directory: '.Hyde::path());

            if (PharSupport::running()) {
                $this->comment('Application binary path: '.get_included_files()[0]);
            }
        }
        $this->newLine();

        $this->line('Enabled features:');
        $this->printEnabledFeatures();

        return Command::SUCCESS;
    }

    protected function printVerbosePathInformation(): void
    {
        $this->line('Project directory:');
        $this->line(' > '.realpath(Hyde::path()));
        $this->line('Framework vendor path:');
        $this->line(' > '.(str_replace('/', DIRECTORY_SEPARATOR, Hyde::vendorPath()).' (vendor)'));
        $this->line(' > '.realpath(Hyde::vendorPath()).' (real)');
    }

    protected function printEnabledFeatures(): void
    {
        /** @var array<\Hyde\Enums\Feature> $features */
        $features = Config::getArray('hyde.features', []);

        foreach ($features as $feature) {
            $this->line(" - $feature->name");
        }
    }
}
