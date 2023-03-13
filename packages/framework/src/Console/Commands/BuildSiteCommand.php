<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Support\BuildWarnings;
use Hyde\Console\Concerns\Command;
use Hyde\Framework\Services\BuildService;
use Hyde\Framework\Services\BuildTaskService;

use function memory_get_peak_usage;
use function number_format;
use function array_search;
use function shell_exec;
use function microtime;
use function sprintf;
use function app;

/**
 * Hyde Command to run the Build Process.
 *
 * @see \Hyde\Framework\Testing\Feature\StaticSiteServiceTest
 */
class BuildSiteCommand extends Command
{
    /** @var string */
    protected $signature = 'build 
        {--run-dev : Run the NPM dev script after build}
        {--run-prod : Run the NPM prod script after build}
        {--run-prettier : Format the output using NPM Prettier}
        {--pretty-urls : Should links in output use pretty URLs?}
        {--no-api : Disable API calls, for example, Torchlight}';

    /** @var string */
    protected $description = 'Build the static site';

    protected BuildService $service;
    protected BuildTaskService $taskService;

    public function handle(): int
    {
        $timeStart = microtime(true);

        $this->title('Building your static site!');

        $this->service = new BuildService($this->output);

        $this->taskService = app(BuildTaskService::class);
        $this->taskService->setOutput($this->output);

        $this->runPreBuildActions();

        $this->service->transferMediaAssets();

        $this->service->compileStaticPages();

        $this->runPostBuildActions();

        $this->printFinishMessage($timeStart);

        return $this->getExitCode();
    }

    protected function runPreBuildActions(): void
    {
        if ($this->option('no-api')) {
            $this->info('Disabling external API calls');
            $this->newLine();
            $config = Config::getArray('hyde.features', []);
            unset($config[array_search('torchlight', $config)]);
            Config::set(['hyde.features' => $config]);
        }

        if ($this->option('pretty-urls')) {
            $this->info('Generating site with pretty URLs');
            $this->newLine();
            Config::set(['hyde.pretty_urls' => true]);
        }

        $this->taskService->runPreBuildTasks();
    }

    public function runPostBuildActions(): void
    {
        $this->taskService->runPostBuildTasks();

        if ($this->option('run-prettier')) {
            $this->runNodeCommand(
                'npx prettier '.Hyde::pathToRelative(Hyde::sitePath()).'/**/*.html --write --bracket-same-line',
                'Prettifying code!',
                'prettify code'
            );
        }

        if ($this->option('run-dev')) {
            $this->runNodeCommand('npm run dev', 'Building frontend assets for development!');
        }

        if ($this->option('run-prod')) {
            $this->runNodeCommand('npm run prod', 'Building frontend assets for production!');
        }
    }

    protected function printFinishMessage(float $timeStart): void
    {
        if ($this->hasWarnings()) {
            $this->newLine();
            $this->error('There were some warnings during the build process:');
            $this->newLine();
            BuildWarnings::writeWarningsToOutput($this->output, $this->output->isVerbose());
        }

        $executionTime = (microtime(true) - $timeStart);
        $this->info(sprintf(
            "\nAll done! Finished in %s seconds (%sms) with %sMB peak memory usage",
            number_format($executionTime, 2),
            number_format($executionTime * 1000, 2),
            number_format(memory_get_peak_usage() / 1024 / 1024, 2)
        ));

        $this->info('Congratulations! 🎉 Your static site has been built!');
        $this->line(
            'Your new homepage is stored here -> '.
            static::fileLink(Hyde::sitePath('index.html'))
        );
    }

    protected function runNodeCommand(string $command, string $message, ?string $actionMessage = null): void
    {
        $this->info($message.' This may take a second.');

        $output = shell_exec(sprintf(
            '%s%s',
            app()->environment() === 'testing' ? 'echo ' : '',
            $command
        ));

        $this->line($output ?? sprintf(
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
            return self::INVALID;
        }

        return Command::SUCCESS;
    }
}
