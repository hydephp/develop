<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;

use function sprintf;

/**
 * Start the realtime compiler server.
 *
 * @see https://github.com/hydephp/realtime-compiler
 */
class ServeCommand extends Command
{
    /** @var string */
    protected $signature = 'serve {--host= : <comment>[default: "localhost"]</comment>}} {--port= : <comment>[default: 8080]</comment>}';

    /** @var string */
    protected $description = 'Start the realtime compiler server.';

    public function handle(): int
    {
        $this->line('<info>Starting the HydeRC server...</info> Press Ctrl+C to stop');

        $this->runServerProcess(sprintf('php -S %s:%d %s',
            $this->getHostSelection(),
            $this->getPortSelection(),
            $this->getExecutablePath()
        ));

        return Command::SUCCESS;
    }

    protected function getPortSelection(): int
    {
        return (int) ($this->option('port') ?: Config::getInt('hyde.server.port', 8080));
    }

    protected function getHostSelection(): string
    {
        return (string) $this->option('host') ?: Config::getString('hyde.server.host', 'localhost');
    }

    protected function getExecutablePath(): string
    {
        return Hyde::path('vendor/hyde/realtime-compiler/bin/server.php');
    }

    /** @codeCoverageIgnore Until output is testable */
    protected function runServerProcess(string $command): void
    {
        Process::forever()->run($command, function (string $type, string $line): void {
            $this->handleOutput($line);
        });
    }

    protected function handleOutput(string $line): void
    {
        $this->output->write($line);
    }
}
