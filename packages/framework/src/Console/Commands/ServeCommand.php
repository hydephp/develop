<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Closure;
use Hyde\Hyde;
use Hyde\Facades\Config;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Hyde\Console\Concerns\Command;
use Hyde\RealtimeCompiler\ConsoleOutput;
use Illuminate\Support\Facades\Process;

use function rtrim;
use function sprintf;
use function in_array;
use function str_replace;
use function class_exists;

/**
 * Start the realtime compiler server.
 *
 * @see https://github.com/hydephp/realtime-compiler
 */
class ServeCommand extends Command
{
    /** @var string */
    protected $signature = 'serve
        {--host= : <comment>[default: "localhost"]</comment>}}
        {--port= : <comment>[default: 8080]</comment>}
        {--save-preview= : Should the served page be saved to disk? (Overrides config setting)}
        {--dashboard= : Enable the realtime compiler dashboard. (Overrides config setting)}
        {--pretty-urls= : Enable pretty URLs. (Overrides config setting)}
        {--play-cdn= : Enable the Tailwind Play CDN. (Overrides config setting)}
        {--open=false : Open the site preview in the browser.}
        {--vite : Enable Vite for Hot Module Replacement (HMR)}
    ';

    /** @var string */
    protected $description = 'Start the realtime compiler server';

    protected ConsoleOutput $console;

    public function safeHandle(): int
    {
        $this->configureOutput();
        $this->printStartMessage();

        if ($this->option('open') !== 'false') {
            $this->openInBrowser((string) $this->option('open'));
        }

        $command = sprintf('php -S %s:%d %s',
            $this->getHostSelection(),
            $this->getPortSelection(),
            $this->getExecutablePath()
        );

        if ($this->option('vite')) {
            $this->runViteProcess();
        }

        $this->runServerProcess($command);

        return Command::SUCCESS;
    }

    protected function runViteProcess(): void
    {
        $viteCommand = 'npm run dev';

        Process::forever()
            ->start($viteCommand, function (string $type, string $line): void {
                $this->output->write($line);
            });

        $this->info('Vite development server started for HMR.');
    }

    protected function getViteEnvironmentVariables(): array
    {
        return Arr::whereNotNull([
            'NODE_ENV' => 'development',
            'PORT' => 3000,
        ]);
    }

    protected function getViteOutputHandler(): Closure
    {
        return function (string $type, string $line): void {
            $this->output->write($line);
        };
    }

    protected function getHostSelection(): string
    {
        return (string) $this->option('host') ?: Config::getString('hyde.server.host', 'localhost');
    }

    protected function getPortSelection(): int
    {
        return (int) ($this->option('port') ?: Config::getInt('hyde.server.port', 8080));
    }

    protected function getExecutablePath(): string
    {
        return Hyde::path('vendor/hyde/realtime-compiler/bin/server.php');
    }

    protected function runServerProcess(string $command): void
    {
        Process::forever()
            ->env($this->getEnvironmentVariables())
            ->start($command, $this->getOutputHandler());

        // Keep the main process running
        while (true) {
            usleep(1000000); // Sleep for 1 second
        }
    }

    protected function getEnvironmentVariables(): array
    {
        return Arr::whereNotNull([
            'HYDE_SERVER_REQUEST_OUTPUT' => ! $this->option('no-ansi'),
            'HYDE_SERVER_SAVE_PREVIEW' => $this->parseEnvironmentOption('save-preview'),
            'HYDE_SERVER_DASHBOARD' => $this->parseEnvironmentOption('dashboard'),
            'HYDE_PRETTY_URLS' => $this->parseEnvironmentOption('pretty-urls'),
            'HYDE_PLAY_CDN' => $this->parseEnvironmentOption('play-cdn'),
        ]);
    }

    protected function configureOutput(): void
    {
        if (! $this->useBasicOutput()) {
            $this->console = new ConsoleOutput($this->output->isVerbose());
        }
    }

    protected function printStartMessage(): void
    {
        $this->useBasicOutput()
            ? $this->output->writeln('<info>Starting the HydeRC server...</info> Press Ctrl+C to stop')
            : $this->console->printStartMessage($this->getHostSelection(), $this->getPortSelection(), $this->getEnvironmentVariables());

        if ($this->option('vite')) {
            $this->console->printViteMessage();
        }
    }

    protected function getOutputHandler(): Closure
    {
        return $this->useBasicOutput() ? function (string $type, string $line): void {
            $this->output->write($line);
        } : $this->console->getFormatter();
    }

    protected function useBasicOutput(): bool
    {
        return $this->option('no-ansi') || ! class_exists(ConsoleOutput::class);
    }

    protected function parseEnvironmentOption(string $name): ?string
    {
        $value = $this->option($name) ?? $this->checkArgvForOption($name);

        if ($value !== null) {
            return match ($value) {
                'true', '' => 'enabled',
                'false' => 'disabled',
                default => throw new InvalidArgumentException(sprintf('Invalid boolean value for --%s option.', $name))
            };
        }

        return null;
    }

    /** Fallback check so that an environment option without a value is acknowledged as true. */
    protected function checkArgvForOption(string $name): ?string
    {
        if (isset($_SERVER['argv'])) {
            if (in_array("--$name", $_SERVER['argv'], true)) {
                return 'true';
            }
        }

        return null;
    }

    protected function openInBrowser(string $path = '/'): void
    {
        $binary = $this->getOpenCommand(PHP_OS_FAMILY);

        $command = sprintf('%s http://%s:%d', $binary, $this->getHostSelection(), $this->getPortSelection());
        $command = rtrim("$command/$path", '/');

        $process = $binary ? Process::command($command)->run() : null;

        if (! $process || $process->failed()) {
            $this->warn('Unable to open the site preview in the browser on your system:');
            $this->line(sprintf('  %s', str_replace("\n", "\n  ", $process ? $process->errorOutput() : "Missing suitable 'open' binary.")));
            $this->newLine();
        }
    }

    protected function getOpenCommand(string $osFamily): ?string
    {
        return match ($osFamily) {
            'Windows' => 'start',
            'Darwin' => 'open',
            'Linux' => 'xdg-open',
            default => null
        };
    }
}
