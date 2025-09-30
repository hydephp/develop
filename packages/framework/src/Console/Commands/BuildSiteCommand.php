<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Support\BuildWarnings;
use Hyde\Console\Concerns\Command;
use Hyde\Framework\Services\BuildService;
use Hyde\Framework\Services\BuildTaskService;
use Illuminate\Support\Facades\Process;

use function memory_get_peak_usage;
use function number_format;
use function array_search;
use function microtime;
use function sprintf;
use function app;
use function Termwind\render;

/**
 * Run the static site build process.
 */
class BuildSiteCommand extends Command
{
    /** @var string */
    protected $signature = 'build
        {--vite : Build frontend assets using Vite}
        {--pretty-urls : Should links in output use pretty URLs?}
        {--no-api : Disable API calls, for example, Torchlight}
        {--run-dev : [Removed] Use --vite instead}
        {--run-prod : [Removed] Use --vite instead}';

    /** @var string */
    protected $description = 'Build the static site';

    protected BuildService $service;
    protected BuildTaskService $taskService;
    protected ?\Hyde\Framework\Services\StyledProgressBar $progressBar = null;

    public function handle(): int
    {
        $this->checkForDeprecatedRunMixCommandUsage();

        $timeStart = microtime(true);

        $this->printStartBanner();

        $this->service = new BuildService($this->output);

        $this->configureBuildTaskService();

        $this->runPreBuildActions();

        $this->service->compileStaticPages();

        $this->runPostBuildActions();

        $this->printFinishMessage($timeStart);

        return $this->getExitCode();
    }

    /**
     * Get or create the shared styled progress bar instance.
     */
    public function getProgressBar(): \Hyde\Framework\Services\StyledProgressBar
    {
        if (! $this->progressBar) {
            $this->progressBar = new \Hyde\Framework\Services\StyledProgressBar($this->output);
        }

        return $this->progressBar;
    }

    protected function printStartBanner(): void
    {
        $version = Hyde::getInstance()->version();
        $lines = [
            '',
            sprintf('<span class="text-blue-500">HydePHP Static Site Builder</span> <span class="text-gray">v%s</span>', $version),
            '<span class="text-gray">Building your static site...</span>',
            '',
        ];

        $this->renderBox($lines, true);
    }

    protected function configureBuildTaskService(): void
    {
        /** @var BuildTaskService $taskService */
        $taskService = app(BuildTaskService::class);

        $this->taskService = $taskService;
        $this->taskService->setOutput($this->output);
    }

    protected function runPreBuildActions(): void
    {
        if ($this->option('no-api')) {
            render('<div class="mx-2"><span class="text-blue-500">ℹ</span> <span class="text-white">Disabling external API calls</span></div>');
            $this->newLine();
            /** @var array<string, string> $config */
            $config = Config::getArray('hyde.features', []);
            unset($config[array_search('torchlight', $config)]);
            Config::set(['hyde.features' => $config]);
        }

        if ($this->option('pretty-urls')) {
            render('<div class="mx-2"><span class="text-blue-500">ℹ</span> <span class="text-white">Generating site with pretty URLs</span></div>');
            $this->newLine();
            Config::set(['hyde.pretty_urls' => true]);
        }

        if ($this->option('vite')) {
            render('<div class="mx-2"><span class="text-blue-500">ℹ</span> <span class="text-white">Building frontend assets</span></div>');
            $this->runNodeCommand('npm run build', 'Building frontend assets for production!');
        }

        $this->taskService->runPreBuildTasks();
    }

    public function runPostBuildActions(): void
    {
        $this->taskService->runPostBuildTasks();
    }

    protected function printFinishMessage(float $timeStart): void
    {
        if ($this->hasWarnings()) {
            $this->newLine();
            render('<div class="mx-2"><span class="text-yellow-500">⚠</span> <span class="text-yellow-500">Build completed with warnings</span></div>');
            $this->newLine();
            BuildWarnings::writeWarningsToOutput($this->output, $this->output->isVerbose());
            $this->newLine();
        }

        $executionTime = (microtime(true) - $timeStart);
        $executionTimeSeconds = number_format($executionTime, 2);
        $executionTimeMs = number_format($executionTime * 1000, 2);
        $memoryPeak = number_format(memory_get_peak_usage() / 1024 / 1024, 2);
        $outputPath = Hyde::sitePath('index.html');

        $lines = [
            '',
            '<span class="text-green-500">✓</span> <span class="text-white">Build completed successfully!</span>',
            '',
            sprintf('<span class="text-white">Time:</span> <span class="text-yellow-500">%ss</span> <span class="text-gray">(%sms)</span>', $executionTimeSeconds, $executionTimeMs),
            sprintf('<span class="text-white">Memory:</span> <span class="text-yellow-500">%sMB</span> <span class="text-gray">peak</span>', $memoryPeak),
            '',
            sprintf('<span class="text-white">Output:</span> <a href="file://%s" class="text-blue-500">%s</a>', $outputPath, $outputPath),
            '',
        ];

        $this->renderBox($lines);
    }

    protected function renderBox(array $lines, bool $center = false): void
    {
        // Use a fixed width for consistency across all boxes
        $boxWidth = 60;

        // Format each line with proper padding
        $formattedLines = array_map(function (string $line) use ($boxWidth, $center): string {
            $strippedLength = mb_strlen(strip_tags($line));

            if ($center && $strippedLength > 0) {
                $totalPadding = $boxWidth - $strippedLength;
                $leftPadding = (int) floor($totalPadding / 2);
                $rightPadding = (int) ceil($totalPadding / 2);

                return sprintf('&nbsp;│&nbsp;%s%s%s&nbsp;│',
                    str_repeat('&nbsp;', $leftPadding),
                    $line,
                    str_repeat('&nbsp;', $rightPadding)
                );
            }

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

    protected function runNodeCommand(string $command, string $message, ?string $actionMessage = null): void
    {
        $this->info($message.' This may take a second.');

        $output = Process::command($command)->run();

        $this->line($output->output() ?? sprintf(
            '<fg=red>Could not %s! Is NPM installed?</>',
            $actionMessage ?? 'run script'
        ));
    }

    protected function hasWarnings(): bool
    {
        return BuildWarnings::hasWarnings() && BuildWarnings::reportsWarnings();
    }

    protected function getExitCode(): int
    {
        if ($this->hasWarnings() && BuildWarnings::reportsWarningsAsExceptions()) {
            return Command::INVALID;
        }

        return Command::SUCCESS;
    }

    /**
     * This method is called when the removed --run-dev or --run-prod options are used.
     *
     * @deprecated Use --vite instead
     * @since v2.0 - This will be removed after 2-3 minor releases depending on the timeframe between them. (~v2.3)
     *
     * @codeCoverageIgnore
     */
    protected function checkForDeprecatedRunMixCommandUsage(): void
    {
        if ($this->option('run-dev') || $this->option('run-prod')) {
            $this->error('The --run-dev and --run-prod options have been removed in HydePHP v2.0.');
            $this->info('Please use --vite instead to build assets for production with Vite.');
            $this->line('See https://github.com/hydephp/develop/pull/2013 for more information.');

            exit(Command::INVALID);
        }
    }
}
