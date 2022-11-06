<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\BuildTasks\PostBuildTasks;

use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\XmlGenerators\RssFeedService;
use Hyde\Hyde;

class GenerateRssFeed extends BuildTask
{
    public static string $description = 'Generating RSS feed';

    public function run(): void
    {
        file_put_contents(
            Hyde::sitePath(RssFeedService::outputFilename()),
            RssFeedService::generateFeed()
        );
    }

    public function then(): void
    {
        $this->createdSiteFile('_site/'.RssFeedService::outputFilename())->withExecutionTime();
    }
}
