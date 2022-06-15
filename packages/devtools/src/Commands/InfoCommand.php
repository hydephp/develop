<?php

namespace Hyde\DevTools\Commands;

use Illuminate\Console\Command;

class InfoCommand extends Command
{
    protected $signature = 'devtools';

    protected $description = 'Print the Hyde DevTools welcome screen';

    public function handle()
    {
        $this->info('Hyde DevTools is installed!');
    }
}
