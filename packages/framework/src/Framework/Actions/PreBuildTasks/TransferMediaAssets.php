<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PreBuildTasks;

use Hyde\Hyde;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Framework\Features\BuildTasks\PreBuildTask;
use Hyde\Framework\Concerns\InteractsWithDirectories;

class TransferMediaAssets extends PreBuildTask
{
    protected static string $message = 'Transferring Media Assets';

    use InteractsWithDirectories;

    public function handle(): void
    {
        $this->needsDirectory(Hyde::siteMediaPath());

        $this->withProgressBar(MediaFile::files(), function (string $identifier): void {
            $sitePath = Hyde::siteMediaPath($identifier);
            $this->needsParentDirectory($sitePath);
            copy(Hyde::mediaPath($identifier), $sitePath);
        });
    }
}
