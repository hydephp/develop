<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Hyde\Console\Concerns\Command;
use Hyde\Support\Internal\RouteListItem;

use function array_map;
use function array_keys;
use function array_values;

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
        $routes = $this->generate();

        $this->table($this->makeHeader($routes), $routes);

        return Command::SUCCESS;
    }

    protected function generate(): array
    {
        return array_map(function (Route $route): array {
            return RouteListItem::format($route);
        }, array_values(Hyde::routes()->all()));
    }

    protected function makeHeader(array $routes): array
    {
        return array_map('\Hyde\make_title', array_keys($routes[0]));
    }
}
