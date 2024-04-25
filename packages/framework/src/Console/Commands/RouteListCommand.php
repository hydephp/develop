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
        [$header, $rows] = $this->extracted();

        $this->table($header, $rows);

        return Command::SUCCESS;
    }

    protected function extracted(): array
    {
        $routes = new RouteList();

        return [$routes->header(), $routes->rows()];
    }
}
