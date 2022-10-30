<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use LaravelZero\Framework\Commands\Command;

/**
 * Start the realtime compiler server.
 *
 * @see https://github.com/hydephp/realtime-compiler
 */
class ServeCommand extends Command
{
    /** @var string */
    protected $signature = 'serve {--port=8080} {--host=localhost}';

    /** @var string */
    protected $description = 'Start the realtime compiler server.';

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
