<?php

declare(strict_types=1);

namespace Hyde\Routing;

use Hyde\Foundation\RouteCollection;
use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Hyde;
use Hyde\Support\Concerns\JsonSerializesArrayable;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use function str_replace;
use Stringable;

/**
 * The Route class bridges the gaps between Hyde pages and their respective compiled static webpages
 * by providing helper methods and information allowing you to easily access and interact with the
 * various paths associated with a page, both source and compiled file paths as well as the URL.
 *
 * If you visualize a web of this class's properties, you should be able to see how this
 * class links them all together, and what powerful information you can gain from it.
 *
 * @see \Hyde\Framework\Testing\Feature\RouteTest
 */
class Route implements Stringable, JsonSerializable, Arrayable
{
    use JsonSerializesArrayable;

    protected HydePage $page;

    public function __construct(HydePage $page)
    {
        $this->page = $page;
    }

    /**
     * Cast a route object into a string that can be used in a href attribute.
     */
    public function __toString(): string
    {
        return $this->getLink();
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'routeKey' => $this->getRouteKey(),
            'sourcePath' => $this->getSourcePath(),
            'outputPath' => $this->getOutputPath(),
            'page' => [
                'class' => $this->getPageClass(),
                'identifier' => $this->getPageIdentifier(),
            ],
        ];
    }

    /**
     * Resolve a site web link to the compiled page.
     * The path is relative to the current page being rendered, and uses pretty URLs if enabled.
     */
    public function getLink(): string
    {
        return Hyde::relativeLink($this->page->getLink());
    }

    public function getPageClass(): string
    {
        return $this->page::class;
    }

    public function getPageIdentifier(): string
    {
        return $this->page->getIdentifier();
    }

    public function getPage(): HydePage
    {
        return $this->page;
    }

    public function getRouteKey(): string
    {
        return $this->page->getRouteKey();
    }

    public function getSourcePath(): string
    {
        return $this->page->getSourcePath();
    }

    public function getOutputPath(): string
    {
        return $this->page->getOutputPath();
    }

    /**
     * Get the qualified URL for the route, using pretty URLs if enabled.
     *
     * @return string Fully qualified URL using the configured base URL.
     */
    public function getQualifiedUrl(): string
    {
        return Hyde::url($this->getOutputPath());
    }

    /**
     * Determine if the route instance matches another route or route key.
     */
    public function is(Route|RouteKey|string $route): bool
    {
        if ($route instanceof Route) {
            return $this->getRouteKey() === $route->getRouteKey();
        }

        return $this->getRouteKey() === (string) $route;
    }

    public static function get(string $routeKey): ?Route
    {
        return Hyde::routes()->get(self::normalizeRouteKey($routeKey));
    }

    public static function getOrFail(string $routeKey): Route
    {
        return Route::get($routeKey) ?? throw new RouteNotFoundException($routeKey);
    }

    public static function all(): RouteCollection
    {
        return Hyde::routes();
    }

    public static function current(): ?Route
    {
        return Hyde::currentRoute();
    }

    public static function home(): ?Route
    {
        return Route::get('index');
    }

    public static function exists(string $routeKey): bool
    {
        return Hyde::routes()->has($routeKey);
    }

    /**
     * Format a route key so both dot and slash notations are supported.
     */
    protected static function normalizeRouteKey(string $routeKey): string
    {
        return str_replace('.', '/', $routeKey);
    }
}
