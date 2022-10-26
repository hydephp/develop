<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use LaravelZero\Framework\Commands\Command;

/**
 * Start the realtime compiler server.
 */
class ServeCommand extends Command
{
    /** @var string */
    protected $signature = 'serve {--port=8080} {--host=localhost}';

    /** @var string */
    protected $description = 'Start the experimental realtime compiler.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->line('<info>Starting the HydeRC server...</info> Press Ctrl+C to stop');

        $port = $this->option('port');
        $host = $this->option('host');
        $command = "php -S $host:$port ".Hyde::path('vendor/hyde/realtime-compiler/bin/server.php');
        if (app()->environment('testing')) {
            $command = 'echo '.$command;
        }
        passthru($command);

        return 0;
    }
}
