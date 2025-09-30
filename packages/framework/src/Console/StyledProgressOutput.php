<?php

declare(strict_types=1);

namespace Hyde\Console;

use function Termwind\render;
use function sprintf;
use function str_repeat;
use function mb_strlen;
use function strip_tags;
use function microtime;
use function number_format;

/**
 * Provides styled progress output using Termwind for build operations.
 */
class StyledProgressOutput
{
    protected float $startTime;
    protected int $boxWidth = 60;

    /** @var array<array{name: string, icon: string, current: int, total: int, complete: bool}> */
    protected array $stages = [];

    protected int $currentStage = -1;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function addStage(string $name, string $icon, int $total): void
    {
        $this->stages[] = [
            'name' => $name,
            'icon' => $icon,
            'current' => 0,
            'total' => $total,
            'complete' => false,
        ];
    }

    public function startStage(int $index): void
    {
        $this->currentStage = $index;
        $this->render();
    }

    public function advanceStage(int $step = 1): void
    {
        if ($this->currentStage >= 0) {
            $this->stages[$this->currentStage]['current'] += $step;
            $this->render();
        }
    }

    public function completeStage(): void
    {
        if ($this->currentStage >= 0) {
            $this->stages[$this->currentStage]['complete'] = true;
            $this->stages[$this->currentStage]['current'] = $this->stages[$this->currentStage]['total'];
            $this->render();
        }
    }

    protected function render(): void
    {
        $lines = ['', '<span class="text-blue-500">Build Pipeline</span>', ''];

        foreach ($this->stages as $index => $stage) {
            // Stage name with icon
            $icon = $stage['complete'] ? '<span class="text-green-500">✓</span>' : $stage['icon'];
            $stageName = sprintf('%s <span class="text-white">%s</span>', $icon, $stage['name']);
            $lines[] = $stageName;

            // Progress bar
            if ($stage['total'] > 0) {
                $percentage = $stage['current'] / $stage['total'];
                $filledWidth = (int) ($percentage * 20);
                $emptyWidth = 20 - $filledWidth;

                $progressBar = sprintf(
                    '  <span class="text-gray">[</span><span class="text-green-500">%s</span><span class="text-gray">%s</span><span class="text-gray">]</span> <span class="text-yellow-500">%d%%</span> <span class="text-gray">(%d/%d)</span>',
                    str_repeat('█', $filledWidth),
                    str_repeat('░', $emptyWidth),
                    (int) ($percentage * 100),
                    $stage['current'],
                    $stage['total']
                );

                $lines[] = $progressBar;
            } elseif ($index === $this->currentStage && ! $stage['complete']) {
                $lines[] = '  <span class="text-blue-500">⟳</span> <span class="text-gray">Processing...</span>';
            }

            $lines[] = '';
        }

        $this->renderBox($lines);
    }

    public function renderSummary(int $totalFiles): void
    {
        $executionTime = microtime(true) - $this->startTime;
        $speed = $executionTime > 0 ? (int) ($totalFiles / $executionTime) : 0;

        $lines = [
            '',
            sprintf('<span class="text-green-500">⚡</span> <span class="text-white">%d files processed in %ss</span> <span class="text-gray">(%d files/sec)</span>',
                $totalFiles,
                number_format($executionTime, 2),
                $speed
            ),
            '',
        ];

        $this->renderBox($lines);
    }

    protected function renderBox(array $lines): void
    {
        // Clear previous output (move cursor up)
        if ($this->currentStage > 0 || ($this->currentStage === 0 && $this->stages[0]['current'] > 0)) {
            $lineCount = $this->calculateLineCount();
            // Move cursor up and clear
            echo "\033[{$lineCount}A\r";
        }

        // Format each line with proper padding
        $formattedLines = array_map(function (string $line): string {
            $strippedLength = mb_strlen(strip_tags($line));
            $padding = $this->boxWidth - $strippedLength;

            return sprintf('&nbsp;│&nbsp;%s%s&nbsp;│',
                $line,
                str_repeat('&nbsp;', $padding)
            );
        }, $lines);

        $topLine = sprintf('&nbsp;╭%s╮', str_repeat('─', $this->boxWidth + 2));
        $bottomLine = sprintf('&nbsp;╰%s╯', str_repeat('─', $this->boxWidth + 2));

        $body = implode('<br>', array_merge([''], [$topLine], $formattedLines, [$bottomLine], ['']));

        render("<div class=\"text-green-500\">$body</div>");
    }

    protected function calculateLineCount(): int
    {
        // Calculate how many lines the previous render took
        $count = 5; // Top border + title + empty line + bottom border + empty line

        foreach ($this->stages as $stage) {
            $count += 2; // Stage name + progress bar/processing
            if ($stage['total'] > 0) {
                $count += 1; // Empty line after progress
            }
        }

        return $count;
    }
}