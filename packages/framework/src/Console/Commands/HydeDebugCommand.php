<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Composer\InstalledVersions;
use Hyde\Hyde;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde Command to print debug information.
 */
class HydeDebugCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'debug';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Print debug info';

    public function __construct()
    {
        parent::__construct();

        if (config('app.env', 'production') !== 'development') {
            $this->setHidden();
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('HydePHP Debug Screen');

        $this->newLine();
        $this->comment('Git Version: '.app('git.version'));
        $this->comment('Hyde Version: '.(InstalledVersions::getPrettyVersion('hyde/hyde') ?: 'unreleased'));
        $this->comment('Framework Version: '.(InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased'));

        $this->newLine();
        $this->comment('App Env: '.app('env'));

        $this->newLine();
        if ($this->getOutput()->isVerbose()) {
            $this->line('Project directory:');
            $this->line(' > '.realpath(Hyde::path()));
            $this->line('Framework vendor path:');
            $this->line(' > '.(str_replace('/', DIRECTORY_SEPARATOR, Hyde::vendorPath()).' (vendor)'));
            $this->line(' > '.realpath(Hyde::vendorPath()).' (real)');
        } else {
            $this->comment('Project directory: '.Hyde::path());
        }

        $this->newLine();

        $this->line('Enabled features:');
        foreach (config('hyde.features') as $feature) {
            $this->line(" - $feature");
        }

        return 0;
    }
}
