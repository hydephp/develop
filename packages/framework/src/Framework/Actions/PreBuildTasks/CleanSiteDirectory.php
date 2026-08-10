<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PreBuildTasks;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\Internal\OutputDirectoryValidator;
use Hyde\Framework\Features\BuildTasks\PreBuildTask;

class CleanSiteDirectory extends PreBuildTask
{
    protected static string $message = 'Removing all files from build directory';

    public function handle(): void
    {
        OutputDirectoryValidator::validate();

        if (Filesystem::isDirectory(Hyde::sitePath())) {
            Filesystem::cleanDirectory(Hyde::sitePath());
        }
    }

    public function printFinishMessage(): void
    {
        $this->newLine();
    }
}
