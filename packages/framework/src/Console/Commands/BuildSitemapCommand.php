<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde command to run the build process for the sitemap.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildSitemapCommandTest
 */
class BuildSitemapCommand extends Command
{
    protected $signature = 'build:sitemap';
    protected $description = 'Generate the sitemap.xml';

    public function handle(): int
    {
        return (new GenerateSitemap($this->output))->handle() ?? 0;
    }
}
