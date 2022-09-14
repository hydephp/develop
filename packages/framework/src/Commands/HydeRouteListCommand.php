<?php

namespace Hyde\Framework\Commands;

use LaravelZero\Framework\Commands\Command;
/**
 * Hyde command to display the list of site routes.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeRouteListCommandTest
 */
class HydeRouteListCommand extends Command
{
    protected $signature = 'route:list';
    protected $description = 'Display all registered routes.';

    public function handle(): int
    {
        //

        return 0;
    }
}
