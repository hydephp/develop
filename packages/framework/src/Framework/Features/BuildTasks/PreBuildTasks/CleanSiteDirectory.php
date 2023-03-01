<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\BuildTasks\PreBuildTasks;

use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\BuildTasks\Contracts\RunsBeforeBuild;

class CleanSiteDirectory extends BuildTask implements RunsBeforeBuild
{
    public function handle(): void
    {
        if (config('hyde.empty_output_directory', true)) {
            $this->warn('Removing all files from build directory.');
            if ($this->isItSafeToCleanOutputDirectory()) {
                array_map('unlink', glob(Hyde::sitePath('*.{html,json}'), GLOB_BRACE));
                File::cleanDirectory(Hyde::siteMediaPath());
            }
        }
    }
}
