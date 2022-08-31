<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateRssFeed;
use Hyde\Framework\Concerns\ActionCommand;

/**
 * Hyde command to run the build process for the RSS feed.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildRssFeedCommandTest
 */
class HydeBuildRssFeedCommand extends ActionCommand
{
    protected $signature = 'build:rss';
    protected $description = 'Generate the RSS feed';

    public function handle(): int
    {
        return (new GenerateRssFeed($this->output))->handle() ?? 0;
    }
}
