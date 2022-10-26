<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateSearch;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde command to run the build process for the documentation search index.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\BuildSearchCommandTest
 */
class BuildSearchCommand extends Command
{
    /** @var string */
    protected $signature = 'build:search';

    /** @var string */
    protected $description = 'Generate the docs/search.json';

    public function handle(): int
    {
        return (new GenerateSearch($this->output))->handle() ?? 0;
    }
}
