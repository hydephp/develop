<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteList;
use Hyde\Support\Models\RouteListItem;
use function file_exists;

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
        $routes = new class extends RouteList
        {
            protected static function routeToListItem(Route $route): RouteListItem
            {
                return new class($route) extends RouteListItem
                {
                    protected function stylePageType(string $class): string
                    {
                        $type = parent::stylePageType($class);
                        return $type;
                    }

                    protected function styleSourcePath(string $path): string
                    {
                        return ($this->isPageDiscoverable())
                            ? $this->link(Command::createClickableFilepath(Hyde::path($path)), $path)
                            : '<fg=gray>none</>';
                    }

                    protected function styleOutputPath(string $path): string
                    {
                        $path = parent::styleOutputPath($path);

                        return file_exists(Hyde::path($path))
                            ? $this->link(Command::createClickableFilepath(Hyde::path($path)), $path)
                            : $path;
                    }

                    protected function link(string $link, string $label): string
                    {
                        return "<href=$link>$label</>";
                    }
                };
            }
        };

        $this->table($routes->headers(), $routes->toArray());

        return Command::SUCCESS;
    }
}
