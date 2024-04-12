<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Hyde;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;

use function Hyde\path_join;
use function file_put_contents;

class GenerateRssFeed extends PostBuildTask
{
    public static string $message = 'Generating RSS feed';

    public function handle(): void
    {
        file_put_contents(
            Hyde::sitePath(RssFeedGenerator::getFilename()),
            RssFeedGenerator::make()
        );
    }

    public function printFinishMessage(): void
    {
        $this->createdSiteFile(path_join(Hyde::getOutputDirectory(), RssFeedGenerator::getFilename()))->withExecutionTime();
    }
}
