<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Hyde;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;

use function file_put_contents;

class GenerateSitemap extends PostBuildTask
{
    public static string $message = 'Generating sitemap';

    protected string $path;

    public function handle(): void
    {
        $this->path = Hyde::sitePath('sitemap.xml');

        file_put_contents(
            $this->path,
            SitemapGenerator::make()
        );
    }

    public function printFinishMessage(): void
    {
        $this->createdSiteFile($this->path)->withExecutionTime();
    }
}
