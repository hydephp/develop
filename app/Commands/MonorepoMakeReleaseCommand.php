<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

/**
 * This command is included in the Hyde Monorepo,
 * but is removed when packaging the Hyde application.
 */
class MonorepoMakeReleaseCommand extends Command
{
    protected $signature = 'monorepo:release';
    protected $description = '🪓 Create a new syndicated release for the Hyde Monorepo';

    public function handle(): int
    {
        $this->title('Creating a new release!');

        return 0;
    }
}
