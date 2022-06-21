<?php

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Contracts\AbstractBuildTask;
use Illuminate\Support\Facades\Artisan;

class GenerateSitemap extends AbstractBuildTask
{
    public static string $description = 'Generating sitemap';

    public function run(): void
    {
        Artisan::call('build:sitemap');
    }

    public function then(): void
    {
        $this->writeln("\n".' > Created <info>sitemap.xml</info> in '.$this->getExecutionTime());
    }
}
