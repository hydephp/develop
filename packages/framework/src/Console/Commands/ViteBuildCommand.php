<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Illuminate\Support\Facades\Process;

class ViteBuildCommand extends Command
{
    /** @var string */
    protected $signature = 'vite';

    /** @var string */
    protected $description = 'Build the Vite assets';

    public function handle(): int
    {
        $this->title('Building Vite Assets');

        $output = Process::run('npx vite build', (function (string $type, string $line): void {
            $this->output->write($line);
        }));

        if ($output->failed()) {
            $this->error('Vite failed to build');

            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('Vite assets built');

        return Command::SUCCESS;
    }
}
