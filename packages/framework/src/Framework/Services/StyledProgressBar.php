<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Illuminate\Console\OutputStyle;

use function str_repeat;
use function sprintf;
use function microtime;
use function number_format;
use function Termwind\render;
use function strip_tags;

/**
 * Provides beautifully styled progress bars using Termwind that match BuildSiteCommand's aesthetic.
 */
class StyledProgressBar
{
    protected OutputStyle $output;
    protected array $stages = [];
    protected ?string $currentStage = null;
    protected int $boxWidth = 60;
    protected bool $started = false;
    protected int $linesRendered = 0;
    protected float $startTime = 0;
    protected float $lastRenderTime = 0;
    protected float $minRenderInterval = 0.1; // Minimum 100ms between renders

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;
        $this->startTime = microtime(true);
        $this->lastRenderTime = microtime(true);
    }

    /**
     * Register a stage with its metadata.
     *
     * @param  string  $key  Unique identifier for the stage
     * @param  string  $label  Display label (e.g., "Transferring Media Assets")
     * @param  string  $icon  Icon to display (e.g., "📦")
     * @param  int  $total  Total items to process
     */
    public function addStage(string $key, string $label, string $icon, int $total): void
    {
        $this->stages[$key] = [
            'label' => $label,
            'icon' => $icon,
            'total' => $total,
            'current' => 0,
            'completed' => false,
        ];
    }

    /**
     * Start tracking a specific stage.
     */
    public function startStage(string $key): void
    {
        $this->currentStage = $key;

        if (! $this->started) {
            $this->started = true;
            $this->render();
        }
    }

    /**
     * Increment progress for the current stage.
     */
    public function advance(int $step = 1): void
    {
        if ($this->currentStage && isset($this->stages[$this->currentStage])) {
            $this->stages[$this->currentStage]['current'] += $step;

            // Only render if enough time has passed or if this completes the stage
            $now = microtime(true);
            $isComplete = $this->stages[$this->currentStage]['current'] >= $this->stages[$this->currentStage]['total'];

            if ($isComplete || ($now - $this->lastRenderTime) >= $this->minRenderInterval) {
                $this->render();
                $this->lastRenderTime = $now;
            }
        }
    }

    /**
     * Mark a stage as complete.
     */
    public function completeStage(string $key): void
    {
        if (isset($this->stages[$key])) {
            $this->stages[$key]['completed'] = true;
            $this->stages[$key]['current'] = $this->stages[$key]['total'];
            $this->render();
        }
    }

    /**
     * Complete all stages and show final summary.
     */
    public function finish(): void
    {
        $this->render(true);
        $this->output->newLine();
    }

    /**
     * Render the progress box with all stages.
     */
    protected function render(bool $final = false): void
    {
        // Move cursor up to overwrite previous render (but only in interactive terminal)
        if ($this->linesRendered > 0 && $this->output->isDecorated()) {
            // Clear the previous output by moving cursor up
            for ($i = 0; $i < $this->linesRendered; $i++) {
                $this->output->write("\033[1A\033[2K"); // Move up one line and clear it
            }
        }

        $lines = [];

        // Title
        $titleLine = sprintf('<span class="text-blue-500">⚡ Build Pipeline</span>');
        $lines[] = $titleLine;
        $lines[] = ''; // Spacing after title

        // Render each stage
        foreach ($this->stages as $key => $stage) {
            $lines = array_merge($lines, $this->renderStage($stage, $final));
        }

        // Remove the last empty spacing line before adding summary or bottom padding
        if (! empty($lines) && $lines[count($lines) - 1] === '') {
            array_pop($lines);
        }

        // Summary line on final render
        if ($final) {
            $lines[] = ''; // Spacing before summary
            $executionTime = number_format(microtime(true) - $this->startTime, 2);
            $totalFiles = array_sum(array_column($this->stages, 'total'));
            $speed = $totalFiles > 0 && $executionTime > 0
                ? number_format($totalFiles / $executionTime, 0)
                : '0';

            $summaryLine = sprintf(
                '<span class="text-green-500">✓</span> <span class="text-white">%d files processed in %ss</span> <span class="text-gray">(%s files/sec)</span>',
                $totalFiles,
                $executionTime,
                $speed
            );
            $lines[] = $summaryLine;
        }

        $this->renderBox($lines);

        // Track how many lines we rendered (for next update)
        if (! $final) {
            $this->linesRendered = count($lines) + 2; // +2 for top and bottom borders
        }
    }

    /**
     * Render a single stage with its progress bar.
     */
    protected function renderStage(array $stage, bool $final): array
    {
        $lines = [];
        $icon = $stage['icon'];
        $label = $stage['label'];
        $current = $stage['current'];
        $total = $stage['total'];
        $completed = $stage['completed'] || $final;

        // Status icon
        $statusIcon = $completed ? '<span class="text-green-500">✓</span>' : $icon;

        // Stage label line
        $labelLine = sprintf('%s <span class="text-white">%s</span>', $statusIcon, $label);
        $lines[] = $labelLine;

        // Progress bar
        if ($total > 0) {
            $percentage = $total > 0 ? ($current / $total) * 100 : 0;
            $progressBar = $this->buildProgressBar($percentage, $completed);
            $countText = sprintf('<span class="text-gray">%d/%d</span>', $current, $total);

            $progressLine = sprintf('   %s %s', $progressBar, $countText);
            $lines[] = $progressLine;
        }

        $lines[] = ''; // Spacing between stages

        return $lines;
    }

    /**
     * Build a colored progress bar string.
     */
    protected function buildProgressBar(float $percentage, bool $completed): string
    {
        $barWidth = 20;
        $filled = (int) round(($percentage / 100) * $barWidth);
        $empty = $barWidth - $filled;

        $color = $completed ? 'text-green-500' : 'text-blue-500';

        $filledBar = $filled > 0
            ? sprintf('<span class="%s">%s</span>', $color, str_repeat('█', $filled))
            : '';

        $emptyBar = $empty > 0
            ? sprintf('<span class="text-gray">%s</span>', str_repeat('░', $empty))
            : '';

        return sprintf('[%s%s]', $filledBar, $emptyBar);
    }

    /**
     * Render content in a styled box matching BuildSiteCommand's aesthetic.
     */
    protected function renderBox(array $lines): void
    {
        $formattedLines = array_map(function (string $line): string {
            // Calculate visual width accounting for emojis and special characters
            $strippedLength = $this->getVisualLength($line);
            $padding = $this->boxWidth - $strippedLength;

            return sprintf('&nbsp;│&nbsp;%s%s&nbsp;│',
                $line,
                str_repeat('&nbsp;', max(0, $padding))
            );
        }, $lines);

        $topLine = sprintf('&nbsp;╭%s╮', str_repeat('─', $this->boxWidth + 2));
        $bottomLine = sprintf('&nbsp;╰%s╯', str_repeat('─', $this->boxWidth + 2));

        $body = implode('<br>', array_merge([$topLine], $formattedLines, [$bottomLine]));

        render("<div class=\"text-green-500\">$body</div>");
    }

    /**
     * Calculate the visual length of a string, accounting for HTML tags and emojis.
     */
    protected function getVisualLength(string $string): int
    {
        // Strip HTML tags
        $stripped = strip_tags($string);

        // Count different character types
        $length = 0;

        // Split into characters for precise counting
        $chars = preg_split('//u', $stripped, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($chars as $char) {
            $ord = mb_ord($char);

            // Block characters (█ = U+2588, ░ = U+2591) - count as 1 each
            if ($ord >= 0x2580 && $ord <= 0x259F) {
                $length += 1;
            }
            // Emoji ranges - count as 2 (they render wider)
            elseif (
                ($ord >= 0x1F300 && $ord <= 0x1F9FF) || // Misc symbols and pictographs
                ($ord >= 0x2600 && $ord <= 0x26FF) ||   // Misc symbols
                ($ord >= 0x2700 && $ord <= 0x27BF)      // Dingbats
            ) {
                $length += 2;
            }
            // Regular characters
            else {
                $length += 1;
            }
        }

        return $length;
    }
}
