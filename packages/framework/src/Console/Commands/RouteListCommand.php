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
        $routes = new class extends RouteList {
            /** @param  class-string<\Hyde\Pages\Concerns\HydePage>  $class */
            protected function styleSourcePath(string $path, string $class): string
            {
                if (! $class::isDiscoverable()) {
                    return '<fg=yellow>dynamic</>';
                }

                return $this->clickablePathLink(Command::createClickableFilepath(Hyde::path($path)), $path);
            }

            protected function styleOutputPath(string $path): string
            {
                if (file_exists(Hyde::sitePath($path))) {
                    return $this->clickablePathLink(Command::createClickableFilepath(Hyde::sitePath($path)), "_site/$path");
                }

                return "_site/$path";
            }

            protected function clickablePathLink(string $link, string $path): string
            {
                return "<href=$link>$path</>";
            }
        };

        $this->table($routes->headers(), $routes->toArray());

        return Command::SUCCESS;
    }
}
