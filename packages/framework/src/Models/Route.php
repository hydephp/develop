<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Concerns\JsonSerializesArrayable;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Framework\Foundation\RouteCollection;
use Hyde\Framework\Hyde;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @see \Hyde\Framework\Testing\Feature\RouteTest
 */
class Route implements \Stringable, \JsonSerializable, Arrayable
{
    use JsonSerializesArrayable;

    /**
     * The source model for the route.
     *
     * @var \Hyde\Framework\Concerns\HydePage
     */
    protected HydePage $sourceModel;

    /**
     * The unique route key for the route.
     *
     * @var string The route key. Generally <output-directory/slug>.
     */
    protected string $routeKey;

    protected string $sourcePath;
    protected string $outputPath;
    protected string $uriPath;

    /**
     * Construct a new Route instance for the given page model.
     *
     * @param  \Hyde\Framework\Concerns\HydePage  $page
     */
    public function __construct(HydePage $page)
    {
        $this->sourceModel = $page;
        $this->routeKey = $page->getRouteKey();
        $this->sourcePath = $page->getSourcePath();
        $this->outputPath = $page->getOutputPath();
        $this->uriPath = $page->getUriPath();
    }

    /**
     * Cast a route object into a string that can be used in a href attribute.
     * Should be the same as getLink().
     */
    public function __toString(): string
    {
        return $this->getLink();
    }

    /**
     * Get the instance as an array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'routeKey' => $this->routeKey,
            'sourceModelPath' => $this->sourceModel->getSourcePath(),
            'sourceModelType' => $this->sourceModel::class,
        ];
    }

    /**
     * Resolve a site web link to the file, using pretty URLs if enabled.
     *
     * @return string Relative URL path to the route site file.
     */
    public function getLink(): string
    {
        return Hyde::relativeLink($this->getOutputFilePath());
    }

    /**
     * Get the page type for the route.
     *
     * @return class-string<\Hyde\Framework\Concerns\HydePage>
     */
    public function getPageType(): string
    {
        return $this->sourceModel::class;
    }

    /**
     * Get the source model for the route.
     *
     * @return \Hyde\Framework\Concerns\HydePage
     */
    public function getSourceModel(): HydePage
    {
        return $this->sourceModel;
    }

    /**
     * Get the unique route key for the route.
     *
     * @return string The route key. Generally <output-directory/slug>.
     */
    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    /**
     * @deprecated Use getSourcePath() instead.
     */
    public function getSourceFilePath(): string
    {
        return $this->getSourcePath();
    }

    /**
     * Get the path to the source file.
     *
     * @return string Path relative to the root of the project.
     */
    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    /**
     * @deprecated Use getOutputPath() instead.
     */
    public function getOutputFilePath(): string
    {
        return $this->getOutputPath();
    }

    /**
     * Get the path to the output file.
     *
     * @return string Path relative to the site output directory.
     */
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    /**
     * Get the qualified URL for the route, using pretty URLs if enabled.
     *
     * @return string Fully qualified URL using the configured base URL.
     */
    public function getQualifiedUrl(): string
    {
        return Hyde::url($this->outputPath);
    }

    /**
     * @param  \Hyde\Framework\Models\Route|string  $route  A route instance or route key string
     */
    public function is(Route|string $route): bool
    {
        if ($route instanceof Route) {
            return $this->getRouteKey() === $route->getRouteKey();
        }

        return $this->getRouteKey() === $route;
    }

    /**
     * Get a route from the Router index for the specified route key.
     *
     * Alias for static::getFromKey().
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Models\Route
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function get(string $routeKey): static
    {
        return static::getFromKey($routeKey);
    }

    /**
     * Get a route from the Router index for the specified route key.
     *
     * @param  string  $routeKey  Example: posts/foo.md
     * @return \Hyde\Framework\Models\Route
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getFromKey(string $routeKey): static
    {
        return Hyde::routes()->get($routeKey) ?? throw new RouteNotFoundException($routeKey);
    }

    /**
     * Get a route from the Router index for the specified source file path.
     *
     * @param  string  $sourceFilePath  Example: _posts/foo.md
     * @return \Hyde\Framework\Models\Route
     *
     * @throws \Hyde\Framework\Exceptions\RouteNotFoundException
     */
    public static function getFromSource(string $sourceFilePath): static
    {
        return Hyde::routes()->first(function (Route $route) use ($sourceFilePath) {
            return $route->getSourceFilePath() === $sourceFilePath;
        }) ?? throw new RouteNotFoundException($sourceFilePath);
    }

    /**
     * Get a route from the Router index for the supplied page model.
     *
     * @param  \Hyde\Framework\Concerns\HydePage  $page
     * @return \Hyde\Framework\Models\Route
     */
    public static function getFromModel(HydePage $page): Route
    {
        return $page->getRoute();
    }

    /**
     * Get all routes from the Router index.
     *
     * @return \Hyde\Framework\Foundation\RouteCollection<\Hyde\Framework\Models\Route>
     */
    public static function all(): RouteCollection
    {
        return Hyde::routes();
    }

    /**
     * Get the current route for the page being rendered.
     */
    public static function current(): Route
    {
        return Hyde::currentRoute() ?? throw new RouteNotFoundException('current');
    }

    /**
     * Get the home route, usually the index page route.
     */
    public static function home(): Route
    {
        return static::getFromKey('index');
    }

    /**
     * Determine if the supplied route key exists in the route index.
     *
     * @param  string  $routeKey
     * @return bool
     */
    public static function exists(string $routeKey): bool
    {
        return Hyde::routes()->has($routeKey);
    }
}
