<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Illuminate\Support\Arr;
use Hyde\Console\Concerns\Command;
use Hyde\Support\Internal\RouteListItem;

use function array_keys;
use function json_encode;
use function array_values;

/**
 * Display the list of site routes.
 */
class RouteListCommand extends Command
{
    /** @var string */
    protected $signature = 'route:list {--format=txt : The output format (txt or json)}';

    /** @var string */
    protected $description = 'Display all the registered routes';

    public function handle(): int
    {
        $routes = $this->generate();

        return match ($this->option('format')) {
            'txt' => $this->table($this->makeHeader($routes), $routes) ?? Command::SUCCESS,
            'json' => $this->writeRaw(json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?? Command::SUCCESS,
            default => $this->error("Invalid format provided. Only 'txt' and 'json' are supported.") ?? Command::FAILURE,
        };
    }

    /** @return array<int, array<string, string>>  */
    protected function generate(): array
    {
        return Arr::map(array_values(Hyde::routes()->all()), RouteListItem::format(...));
    }

    /** @param array<int, array<string, string>> $routes */
    protected function makeHeader(array $routes): array
    {
        return Arr::map(array_keys($routes[0]), Hyde::makeTitle(...));
    }

    /** Write a message without ANSI formatting */
    protected function writeRaw(string $message): void
    {
        $this->output->setDecorated(false);
        $this->output->writeln($message);
    }
}
