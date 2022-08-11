<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\RssFeedService;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde Command to run the Build Process for the RSS Feed.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildRssFeedCommandTest
 */
class HydeBuildRssFeedCommand extends Command
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
        /** @var float $actionTime */
        $actionTime = microtime(true);

        if (! Features::rss()) {
            $this->error('Cannot generate an RSS feed, please check your configuration.');

            return 1;
        }

        $this->comment('Generating RSS feed...');
        file_put_contents(Hyde::getSiteOutputPath(RssFeedService::getDefaultOutputFilename()), RssFeedService::generateFeed());
        $this->line(' > Created <info>'.RssFeedService::getDefaultOutputFilename().'</> in '.$this->getExecutionTimeInMs($actionTime)."ms\n");

        return 0;
    }

    protected function getExecutionTimeInMs(float $timeStart): string
    {
        return number_format(((microtime(true) - $timeStart) * 1000), 2);
    }
}
