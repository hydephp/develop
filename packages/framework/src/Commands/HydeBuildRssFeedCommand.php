<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Concerns\ActionCommand;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\RssFeedService;

/**
 * Hyde Command to run the Build Process for the RSS Feed.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildRssFeedCommandTest
 */
class HydeBuildRssFeedCommand extends ActionCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'build:rss {--no-api : (Not yet supported)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate the RSS feed';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (! Features::rss()) {
            $this->error('Cannot generate an RSS feed, please check your configuration.');
            return 1;
        }

        $this->action('Generating RSS feed', function () {
            file_put_contents(
                Hyde::getSiteOutputPath(RssFeedService::getDefaultOutputFilename()),
                RssFeedService::generateFeed()
            );
        }, sprintf("Created <info>%s</info>", RssFeedService::getDefaultOutputFilename()));

        return 0;
    }
}
