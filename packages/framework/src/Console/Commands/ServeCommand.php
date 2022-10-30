<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use LaravelZero\Framework\Commands\Command;
use function app;
use function config;
use function passthru;
use function sprintf;

/**
 * Start the realtime compiler server.
 *
 * @see https://github.com/hydephp/realtime-compiler
 */
class ServeCommand extends Command
{
    /** @var string */
    protected $signature = 'serve {--host=localhost} {--port=}';

    /** @var string */
    protected $description = 'Start the realtime compiler server.';

    public function handle(): int
    {
        $this->line('<info>Starting the HydeRC server...</info> Press Ctrl+C to stop');

        $this->runServerCommand(sprintf("php -S %s:%d %s",
            $this->option('host'),
            $this->getPort(),
            $this->getExecutablePath()
        ));

        return Command::SUCCESS;
    }

    protected function getPort(): int
    {
        $port = $this->option('port');
        if (! $port) {
            $port = config('hyde.server.port', 8080);
        }
        return (int) $port ?: 8080;
    }

    protected function getExecutablePath(): string
    {
        return Hyde::path('vendor/hyde/realtime-compiler/bin/server.php');
    }

    protected function runServerCommand(string $command): void
    {
        if (app()->environment('testing')) {
            $this->line($command);
        } else {
            /** @codeCoverageIgnore */
            passthru($command);
        }
    }
}
