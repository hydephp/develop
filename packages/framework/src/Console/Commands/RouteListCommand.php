<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteList;
use Hyde\Support\Models\RouteListItem;

use function file_exists;
use function sprintf;

/**
 * Display the list of site routes.
 */
class RouteListCommand extends Command
{
    /** @var string */
    protected $signature = 'route:list';

    /** @var string */
    protected $description = 'Display all registered routes.';

    public function handle(): int
    {
        $routes = $this->routeListClass();

        $this->table($routes->headers(), $routes->toArray());

        return Command::SUCCESS;
    }

    protected function routeListClass(): RouteList
    {
        return new class extends RouteList
        {
            protected static function routeToListItem(Route $route): RouteListItem
            {
                return new class($route) extends RouteListItem
                {
                    protected function stylePageType(string $class): string
                    {
                        $type = parent::stylePageType($class);

                        $page = $this->route->getPage();
                        /** @experimental The typeLabel macro is experimental */
                        if ($page instanceof InMemoryPage && $page->hasMacro('typeLabel')) {
                            $type .= sprintf(' <fg=gray>(%s)</>', (string) $page->__call('typeLabel', []));
                        }

                        return $type;
                    }

                    protected function styleSourcePath(string $path): string
                    {
                        return parent::styleSourcePath($path) !== 'none'
                            ? $this->href(Command::fileLink(Hyde::path($path)), $path)
                            : '<fg=gray>none</>';
                    }

                    protected function styleOutputPath(string $path): string
                    {
                        return file_exists(Hyde::sitePath($path))
                            ? $this->href(Command::fileLink(Hyde::sitePath($path)), parent::styleOutputPath($path))
                            : parent::styleOutputPath($path);
                    }

                    protected function href(string $link, string $label): string
                    {
                        return "<href=$link>$label</>";
                    }
                };
            }
        };
    }
}
