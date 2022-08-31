<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateSearch;
use Hyde\Framework\Helpers\Features;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde command to run the build process for the documentation search index.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildSearchCommandTest
 */
class HydeBuildSearchCommand extends Command
{
    protected $signature = 'build:search';
    protected $description = 'Generate the docs/search.json';

    public function handle(): int
    {

        if (! Features::rss()) {
            $this->error('Could not generate the search index, please check your configuration.');
            return 1;
        }

        return (new GenerateSearch($this->output))->handle() ?? 0;
    }
}
