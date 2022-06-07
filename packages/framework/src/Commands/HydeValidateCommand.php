<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Hyde;
use LaravelZero\Framework\Commands\Command;

/**
 * Runs the validation tests.
 */
class HydeValidateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'validate';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run a series of tests to validate your setup and help you optimize your site.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Running validation tests!');

        $this->line(shell_exec(Hyde::path('vendor/bin/pest').' --group=validators'));

        $this->info('All done!');

        return 0;
    }
}
