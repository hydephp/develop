<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Hyde\Support\Models\RouteList;
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
            /** @param  class-string<\Hyde\Pages\Concerns\HydePage>  $class */
            protected function styleSourcePath(string $path, string $class): string
            {
                return $class::isDiscoverable()
                    ? $this->link(Command::createClickableFilepath(Hyde::path($path)), $path)
                    : '<fg=yellow>dynamic</>';
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

        $this->table($routes->headers(), $routes->toArray());

        return Command::SUCCESS;
    }
}
