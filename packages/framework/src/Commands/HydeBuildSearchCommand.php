<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateSearch;
use Hyde\Framework\Concerns\ActionCommand;

/**
 * Hyde command to run the build process for the documentation search index.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildSearchCommandTest
 */
class HydeBuildSearchCommand extends ActionCommand
{
    protected $signature = 'build:search';
    protected $description = 'Generate the docs/search.json';

    public function handle()
    {
        (new GenerateSearch($this->output))->handle();
    }
}
