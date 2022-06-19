<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\ChecksIfConfigIsUpToDate;
use Hyde\Framework\Hyde;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

/**
 * Publish the Hyde Config Files.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeUpdateConfigsCommandTest
 */
class HydeUpdateConfigsCommand extends Command
{
    protected $signature = 'update:configs';
    protected $description = 'Publish the default configuration files';

    public function __construct()
    {
        parent::__construct();

        if ($this->checkIfConfigIsOutOfDate() && config('hyde.warn_about_outdated_config', true)) {
            $this->setDescription(
                '<comment>⚠ Your configuration may be out of date. </comment>'.
                'Run this command to update them.'
            );
        }
    }

    public function handle(): int
    {
        File::copyDirectory(Hyde::vendorPath('config'), Hyde::path('config'));

        $this->line('<info>Published config files to</info> <comment>'.Hyde::path('config').'</comment>');

        return 0;
    }

    protected function checkIfConfigIsOutOfDate(): bool
    {
        return ! (new ChecksIfConfigIsUpToDate)->execute();
    }
}
