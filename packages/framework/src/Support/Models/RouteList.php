<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Hyde;
use Illuminate\Contracts\Support\Arrayable;

use function array_keys;
use function array_map;
use function collect;
use function str_replace;
use function ucwords;

/**
 * @internal This class is experimental and is subject to change.
 *
 * @experimental This class is experimental and is subject to change.
 */
class RouteList implements Arrayable
{
    /** @var array<integer, array<string, string>> */
    protected array $routes;

    public function __construct()
    {
        $this->routes = $this->generate();
    }

    public function toArray(): array
    {
        return $this->routes;
    }

    public function headers(): array
    {
        return array_map(function (string $key): string {
            return ucwords(str_replace('_', ' ', $key));
        }, array_keys($this->routes[0]));
    }

    protected function generate(): array
    {
        return collect(Hyde::routes())->map(function (Route $route): array {
            return $this->routeToListItem($route)->toArray();
        })->values()->toArray();
    }

    protected static function routeToListItem(Route $route): RouteListItem
    {
        return new RouteListItem($route);
    }
}
