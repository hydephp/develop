<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;

class ViteBuildCommand extends Command
{
    /** @var string */
    protected $signature = 'vite';

    /** @var string */
    protected $description = 'Build the Vite assets';

    public function handle(): int
    {
        $this->title('Building Vite Assets');

        //

        $this->info('Vite assets built');

        return Command::SUCCESS;
    }
}
