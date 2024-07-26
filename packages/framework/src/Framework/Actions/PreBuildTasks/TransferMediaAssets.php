<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PreBuildTasks;

use Hyde\Support\Filesystem\MediaFile;
use Hyde\Framework\Features\BuildTasks\PreBuildTask;
use Hyde\Framework\Concerns\InteractsWithDirectories;

class TransferMediaAssets extends PreBuildTask
{
    protected static string $message = 'Transferring Media Assets';

    use InteractsWithDirectories;

    public function handle(): void
    {
        $this->needsDirectory(MediaFile::outputPath());

        $this->newLine();

        $this->withProgressBar(MediaFile::files(), function (string $identifier): void {
            $sitePath = MediaFile::outputPath($identifier);
            $this->needsParentDirectory($sitePath);
            copy(MediaFile::sourcePath($identifier), $sitePath);
        });

        $this->newLine();
    }

    public function printFinishMessage(): void
    {
        // We don't need a finish message for this task.
    }
}
