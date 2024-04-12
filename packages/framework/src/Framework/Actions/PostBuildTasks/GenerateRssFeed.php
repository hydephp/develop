<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Hyde;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;

use function file_put_contents;

class GenerateRssFeed extends PostBuildTask
{
    public static string $message = 'Generating RSS feed';

    protected string $path;

    public function handle(): void
    {
        $this->path = Hyde::sitePath(RssFeedGenerator::getFilename());

        file_put_contents(
            $this->path,
            RssFeedGenerator::make()
        );
    }

    public function printFinishMessage(): void
    {
        $this->createdSiteFile($this->path)->withExecutionTime();
    }
}
