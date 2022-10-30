<?php

declare(strict_types=1);

namespace Hyde\Framework\Commands;

use Hyde\Framework\Hyde;
use LaravelZero\Framework\Commands\Command;

/**
 * Start the realtime compiler server.
 */
class HydeServeCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'serve {--port=} {--host=localhost}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start the experimental realtime compiler.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->line('<info>Starting the HydeRC server...</info> Press Ctrl+C to stop');

        $host = $this->option('host');
        $port = $this->option('port');
        if (! $port) {
            $port = config('hyde.server.port', 8080);
        }

        $command = "php -S $host:$port ".Hyde::path('vendor/hyde/realtime-compiler/bin/server.php');
        if (app()->environment('testing')) {
            $command = 'echo '.$command;
        }
        passthru($command);

        return Command::SUCCESS;
    }
}
