<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\BuildTasks\PreBuildTasks;

use Hyde\Hyde;
use Hyde\Facades\Site;
use Hyde\Framework\Features\BuildTasks\PreBuildTask;
use Hyde\Framework\Features\BuildTasks\Contracts\RunsBeforeBuild;
use Illuminate\Support\Facades\File;
use function basename;
use function config;
use function in_array;
use function sprintf;

class CleanSiteDirectory extends PreBuildTask
{
    protected static string $message = 'Removing all files from build directory';

    public function handle(): void
    {
        if (config('hyde.empty_output_directory', true)) {
            if ($this->isItSafeToCleanOutputDirectory()) {
                array_map('unlink', glob(Hyde::sitePath('*.{html,json}'), GLOB_BRACE));
                File::cleanDirectory(Hyde::siteMediaPath());
            }
        }
    }

    public function printFinishMessage(): void
    {
        $this->newLine();
    }

    protected function isItSafeToCleanOutputDirectory(): bool
    {
        if (! $this->isOutputDirectoryWhitelisted() && ! $this->askIfUnsafeDirectoryShouldBeEmptied()) {
            $this->info('Output directory will not be emptied.');

            return false;
        }

        return true;
    }

    protected function isOutputDirectoryWhitelisted(): bool
    {
        return in_array(basename(Hyde::sitePath()), $this->safeOutputDirectories());
    }

    protected function askIfUnsafeDirectoryShouldBeEmptied(): bool
    {
        return $this->confirm(sprintf(
            'The configured output directory (%s) is potentially unsafe to empty. '.
            'Are you sure you want to continue?',
            Site::getOutputDirectory()
        ));
    }

    protected function safeOutputDirectories(): array
    {
        return config('hyde.safe_output_directories', ['_site', 'docs', 'build']);
    }
}
