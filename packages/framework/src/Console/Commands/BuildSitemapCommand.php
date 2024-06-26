<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap;
use LaravelZero\Framework\Commands\Command;

/**
 * Run the build process for the sitemap.
 */
class BuildSitemapCommand extends Command
{
    /** @var string */
    protected $signature = 'build:sitemap';

    /** @var string */
    protected $description = 'Generate the sitemap.xml file';

    public function handle(): int
    {
        return (new GenerateSitemap())->run($this->output);
    }
}
