<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap;
use Hyde\Framework\Concerns\ActionCommand;

/**
 * Hyde command to run the build process for the sitemap.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildSitemapCommandTest
 */
class HydeBuildSitemapCommand extends ActionCommand
{
    protected $signature = 'build:sitemap';
    protected $description = 'Generate the sitemap.xml';

    public function handle(): int
    {
        return (new GenerateSitemap($this->output))->handle() ?? 0;
    }
}
