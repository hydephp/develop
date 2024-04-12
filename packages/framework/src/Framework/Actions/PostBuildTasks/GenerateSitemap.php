<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Hyde;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;

use function Hyde\path_join;
use function file_put_contents;

class GenerateSitemap extends PostBuildTask
{
    public static string $message = 'Generating sitemap';

    public function handle(): void
    {
        file_put_contents(
            Hyde::sitePath('sitemap.xml'),
            SitemapGenerator::make()
        );
    }

    public function printFinishMessage(): void
    {
        $this->createdSiteFile(path_join(Hyde::getOutputDirectory(), 'sitemap.xml'))->withExecutionTime();
    }
}
