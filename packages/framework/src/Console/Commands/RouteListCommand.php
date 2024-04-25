<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Support\Internal\RouteList;

/**
 * Display the list of site routes.
 */
class RouteListCommand extends Command
{
    /** @var string */
    protected $signature = 'route:list';

    /** @var string */
    protected $description = 'Display all the registered routes';

    public function handle(): int
    {
        $routes = new RouteList();

        $this->table($routes->header(), $routes->rows());

        return Command::SUCCESS;
    }
}
