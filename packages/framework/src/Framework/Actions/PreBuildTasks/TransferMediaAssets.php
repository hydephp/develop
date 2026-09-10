<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PreBuildTasks;

use RuntimeException;
use Hyde\Facades\Config;
use Hyde\Facades\Filesystem;
use Hyde\Support\Filesystem\MediaFile;
use Symfony\Component\Console\Command\Command;
use Hyde\Framework\Features\BuildTasks\PreBuildTask;
use Hyde\Framework\Concerns\InteractsWithDirectories;

use function implode;
use function array_map;

class TransferMediaAssets extends PreBuildTask
{
    protected static string $message = 'Transferring Media Assets';

    use InteractsWithDirectories;

    public function handle(): void
    {
        $this->newLine();

        $files = MediaFile::all();

        if (Config::getBool('hyde.load_app_styles_from_cdn', false)) {
            $files->forget('app.css');
        }

        if ($files->isEmpty()) {
            $this->skip("No media files to transfer.\n");
        }

        $failures = [];

        $this->withProgressBar($files, function (MediaFile $file) use (&$failures): void {
            $sitePath = $file->getOutputPath();
            $this->needsParentDirectory($sitePath);

            if (! Filesystem::copy($file->getPath(), $sitePath)) {
                $failures[] = "[{$file->getPath()}] to [{$sitePath}]";
            }
        });

        $this->newLine();

        if ($failures !== []) {
            throw new RuntimeException(
                "Failed to copy media file(s):\n".implode("\n", array_map(fn (string $failure): string => "- $failure", $failures)),
                Command::FAILURE
            );
        }
    }

    public function printFinishMessage(): void
    {
        // We don't need a finish message for this task.
    }
}
