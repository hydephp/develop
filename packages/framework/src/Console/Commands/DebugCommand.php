<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Composer\InstalledVersions;
use LaravelZero\Framework\Commands\Command;

use function str_replace;
use function realpath;
use function app;

/**
 * Hyde Command to print debug information.
 */
class DebugCommand extends Command
{
    /** @var string */
    protected $signature = 'debug';

    /** @var string */
    protected $description = 'Print debug info';

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

        $this->comment('Git Version: '.(string) app('git.version'));
        $this->comment('Hyde Version: '.((InstalledVersions::isInstalled('hyde/hyde') ? InstalledVersions::getPrettyVersion('hyde/hyde') : null) ?: 'unreleased'));
        $this->comment('Framework Version: '.(InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased'));
        $this->newLine();

        $this->comment('App Env: '.(string) app('env'));
        $this->newLine();

        if ($this->output->isVerbose()) {
            $this->printVerbosePathInformation();
        } else {
            $this->comment('Project directory: '.Hyde::path());
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
        foreach (Config::getArray('hyde.features') as $feature) {
            $this->line(" - $feature");
        }
    }
}
