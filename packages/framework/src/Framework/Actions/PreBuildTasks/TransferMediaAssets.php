<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PreBuildTasks;

use Hyde\Facades\Config;
use Hyde\Facades\Filesystem;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Framework\Features\BuildTasks\PreBuildTask;
use Hyde\Framework\Concerns\InteractsWithDirectories;

use function Termwind\render;
use function sprintf;
use function str_repeat;
use function mb_strlen;
use function strip_tags;

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

        $total = $files->count();
        $current = 0;

        $this->renderProgressBox('Transferring Media Assets', '📦', $current, $total);

        foreach ($files as $file) {
            $sitePath = $file->getOutputPath();
            $this->needsParentDirectory($sitePath);
            Filesystem::putContents($sitePath, $file->getContents());

            $current++;
            $this->renderProgressBox('Transferring Media Assets', '📦', $current, $total, true);
        }

        $this->newLine();
    }

    protected function renderProgressBox(string $name, string $icon, int $current, int $total, bool $update = false): void
    {
        if ($update && $current > 0) {
            // Move cursor up to redraw
            echo "\033[7A\r";
        }

        $lines = ['', sprintf('<span class="text-blue-500">%s</span> <span class="text-white">%s</span>', $icon, $name), ''];

        if ($total > 0) {
            $percentage = $current / $total;
            $filledWidth = (int) ($percentage * 20);
            $emptyWidth = 20 - $filledWidth;

            $icon = $current === $total ? '<span class="text-green-500">✓</span>' : '<span class="text-blue-500">⟳</span>';

            $progressBar = sprintf(
                '%s <span class="text-gray">[</span><span class="text-green-500">%s</span><span class="text-gray">%s</span><span class="text-gray">]</span> <span class="text-yellow-500">%d%%</span> <span class="text-gray">(%d/%d)</span>',
                $icon,
                str_repeat('█', $filledWidth),
                str_repeat('░', $emptyWidth),
                (int) ($percentage * 100),
                $current,
                $total
            );

            $lines[] = $progressBar;
        }

        $lines[] = '';

        $this->renderBox($lines);
    }

    protected function renderBox(array $lines): void
    {
        $boxWidth = 60;

        $formattedLines = array_map(function (string $line) use ($boxWidth): string {
            $strippedLength = mb_strlen(strip_tags($line));
            $padding = $boxWidth - $strippedLength;

            return sprintf('&nbsp;│&nbsp;%s%s&nbsp;│',
                $line,
                str_repeat('&nbsp;', $padding)
            );
        }, $lines);

        $topLine = sprintf('&nbsp;╭%s╮', str_repeat('─', $boxWidth + 2));
        $bottomLine = sprintf('&nbsp;╰%s╯', str_repeat('─', $boxWidth + 2));

        $body = implode('<br>', array_merge([''], [$topLine], $formattedLines, [$bottomLine], ['']));

        render("<div class=\"text-green-500\">$body</div>");
    }

    public function printFinishMessage(): void
    {
        // We don't need a finish message for this task.
    }
}
