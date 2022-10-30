<?php

declare(strict_types=1);

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateSearch;
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
        return (new GenerateSearch($this->output))->handle() ?? Command::SUCCESS;
    }
}
