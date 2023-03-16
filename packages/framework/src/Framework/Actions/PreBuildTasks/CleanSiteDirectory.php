<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PreBuildTasks;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Framework\Features\BuildTasks\PreBuildTask;
use Illuminate\Support\Facades\File;

use function array_map;
use function basename;
use function glob;
use function in_array;
use function sprintf;

class CleanSiteDirectory extends PreBuildTask
{
    protected static string $message = 'Removing all files from build directory';

    public function handle(): void
    {
        if ($this->isItSafeToCleanOutputDirectory()) {
            array_map('unlink', (array) glob(Hyde::sitePath('*.{html,json}'), GLOB_BRACE));
            File::cleanDirectory(Hyde::siteMediaPath());
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
            Hyde::getOutputDirectory()
        ));
    }

    protected function safeOutputDirectories(): array
    {
        return Config::getArray('hyde.safe_output_directories', ['_site', 'docs', 'build']);
    }
}
