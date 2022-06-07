<?php

namespace Hyde\Framework\Concerns\Commands;

use Illuminate\Support\Facades\Artisan;

/**
 * Used in Commands to ask the user if they want to rebuild the site, and if so, rebuild it.
 */
trait AsksToRebuildSite
{
    protected function askToRebuildSite(): void
    {
        if ($this->option('no-interaction')) {
            return;
        }

        if ($this->confirm('Would you like to rebuild the site?', 'Yes')) {
            $this->line('Okay, building site!');
            Artisan::call('build');
            $this->info('Site is built!');
        } else {
            $this->line('Okay, you can always run the build later!');
        }
    }
}
