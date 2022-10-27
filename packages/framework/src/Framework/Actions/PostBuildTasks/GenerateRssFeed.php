<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Features\BuildTasks\AbstractBuildTask;
use Hyde\Framework\Services\RssFeedService;
use Hyde\Hyde;

class GenerateRssFeed extends AbstractBuildTask
{
    public static string $description = 'Generating RSS feed';

    public function run(): void
    {
        file_put_contents(
            Hyde::sitePath(RssFeedService::getDefaultOutputFilename()),
            RssFeedService::generateFeed()
        );
    }

    public function then(): void
    {
        $this->createdSiteFile('_site/'.RssFeedService::getDefaultOutputFilename())->withExecutionTime();
    }
}
