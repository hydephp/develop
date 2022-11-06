<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\BuildTasks\PostBuildTasks;

use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;
use Hyde\Hyde;

class GenerateSitemap extends BuildTask
{
    public static string $description = 'Generating sitemap';

    public function run(): void
    {
        file_put_contents(
            Hyde::sitePath('sitemap.xml'),
            SitemapGenerator::make()
        );
    }

    public function then(): void
    {
        $this->createdSiteFile('_site/sitemap.xml')->withExecutionTime();
    }
}
