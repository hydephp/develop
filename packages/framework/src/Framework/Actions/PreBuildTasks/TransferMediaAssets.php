<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PreBuildTasks;

use Hyde\Facades\Config;
use Hyde\Facades\Filesystem;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Framework\Features\BuildTasks\PreBuildTask;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Services\StyledProgressBar;

use function app;

class TransferMediaAssets extends PreBuildTask
{
    protected static string $message = 'Transferring Media Assets';

    use InteractsWithDirectories;

    public function handle(): void
    {
        $files = MediaFile::all();

        if (Config::getBool('hyde.load_app_styles_from_cdn', false)) {
            $files->forget('app.css');
        }

        if ($files->isEmpty()) {
            $this->skip("No media files to transfer.\n");
        }

        // Get progress bar from task service if available
        $progressBar = app(\Hyde\Framework\Services\BuildTaskService::class)->getProgressBar();

        if ($progressBar) {
            // Use unified progress bar
            $progressBar->addStage('media', 'Transferring Media Assets', '📦', $files->count());
            $progressBar->startStage('media');

            foreach ($files as $file) {
                $sitePath = $file->getOutputPath();
                $this->needsParentDirectory($sitePath);
                Filesystem::putContents($sitePath, $file->getContents());
                $progressBar->advance();
            }

            $progressBar->completeStage('media');
        } else {
            // Fallback to old style
            $this->newLine();

            $this->withProgressBar($files, function (MediaFile $file): void {
                $sitePath = $file->getOutputPath();
                $this->needsParentDirectory($sitePath);
                Filesystem::putContents($sitePath, $file->getContents());
            });

            $this->newLine();
        }
    }

    public function printFinishMessage(): void
    {
        // We don't need a finish message for this task.
    }

    public function printStartMessage(): void
    {
        // Check if we're using unified progress bar
        $progressBar = app(\Hyde\Framework\Services\BuildTaskService::class)->getProgressBar();

        if (! $progressBar) {
            // Only show start message if not using unified progress bar
            parent::printStartMessage();
        }
    }
}
