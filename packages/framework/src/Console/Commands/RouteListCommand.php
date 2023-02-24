<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Support\Models\RouteList;

/**
 * Hyde command to display the list of site routes.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\RouteListCommandTest
 */
class RouteListCommand extends Command
{
    /** @var string */
    protected $signature = 'route:list';

    /** @var string */
    protected $description = 'Display all registered routes.';

    public function handle(): int
    {
        $this->table(array_keys($this->getRoutes()[0]), $this->getRoutes());

        return Command::SUCCESS;
    }

    protected function getRoutes(): array
    {
        return (new RouteList())->styleForConsole()->toArray();
    }
}
