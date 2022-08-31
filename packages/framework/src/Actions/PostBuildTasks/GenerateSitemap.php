<?php

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Contracts\AbstractBuildTask;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\SitemapService;

class GenerateSitemap extends AbstractBuildTask
{
    public static string $description = 'Generating sitemap';

    public function run(): void
    {
        file_put_contents(
            Hyde::getSiteOutputPath('sitemap.xml'),
            SitemapService::generateSitemap()
        );
    }

    public function then(): void
    {
        $this->writeln("\n".' > Created <info>sitemap.xml</info> in '.$this->getExecutionTime());
    }
}
