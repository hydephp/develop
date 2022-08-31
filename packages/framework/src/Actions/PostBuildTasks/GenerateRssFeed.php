<?php

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Contracts\AbstractBuildTask;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\RssFeedService;

class GenerateRssFeed extends AbstractBuildTask
{
    public static string $description = 'Generating RSS feed';

    public function run(): void
    {
        if (! Features::rss()) {
            $this->error('Cannot generate an RSS feed, please check your configuration.');
            return;
        }

        file_put_contents(
            Hyde::getSiteOutputPath(RssFeedService::getDefaultOutputFilename()),
            RssFeedService::generateFeed()
        );
    }

    public function then(): void
    {
        $this->writeln(sprintf("\n > Created <info>%s</info> in %s",
            RssFeedService::getDefaultOutputFilename(),
            $this->getExecutionTime())
        );
    }
}
