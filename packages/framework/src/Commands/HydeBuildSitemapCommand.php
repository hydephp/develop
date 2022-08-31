<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap;
use Hyde\Framework\Helpers\Features;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde command to run the build process for the sitemap.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildSitemapCommandTest
 */
class HydeBuildSitemapCommand extends Command
{
    protected $signature = 'build:sitemap';
    protected $description = 'Generate the sitemap.xml';

    public function handle(): int
    {
        if (! Features::sitemap()) {
            $this->error('Could not generate the sitemap, please check your configuration.');
            return 1;
        }

        return (new GenerateSitemap($this->output))->handle() ?? 0;
    }
}
