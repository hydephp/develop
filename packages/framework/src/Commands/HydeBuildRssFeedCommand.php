<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateRssFeed;
use Hyde\Framework\Helpers\Features;
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
        if (! Features::rss()) {
            $this->error('Could not generate the RSS feed, please check your configuration.');
            return 1;
        }

        return (new GenerateRssFeed($this->output))->handle() ?? 0;
    }
}
