<?php

declare(strict_types=1);

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateRssFeed;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde command to run the build process for the RSS feed.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildRssFeedCommandTest
 */
class HydeBuildRssFeedCommand extends Command
{
    protected $signature = 'build:rss';
    protected $description = 'Generate the RSS feed';

    public function handle(): int
    {
        return (new GenerateRssFeed($this->output))->handle() ?? Command::SUCCESS;
    }
}
