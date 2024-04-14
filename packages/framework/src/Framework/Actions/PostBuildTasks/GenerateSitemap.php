<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Hyde;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;

use function file_put_contents;

class GenerateSitemap extends PostBuildTask
{
    use InteractsWithDirectories;

    public static string $message = 'Generating sitemap';

    protected string $path;

    public function handle(): void
    {
        if (blank(Hyde::url()) || str_starts_with(Hyde::url(), 'http://localhost')) {
            $this->skip('Cannot generate sitemap without a valid base URL');
        }

        $this->path = Hyde::sitePath('sitemap.xml');

        $this->needsParentDirectory($this->path);

        file_put_contents($this->path, SitemapGenerator::make());
    }

    public function printFinishMessage(): void
    {
        $this->createdSiteFile($this->path)->withExecutionTime();
    }
}
