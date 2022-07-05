<?php

namespace Hyde\Framework\Modules\Routing;

use Hyde\Framework\Hyde;
use Hyde\Framework\Contracts\PageContract;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\RouteTest
 */
class Route implements RouteContract, RouteFacadeContract
{
    /**
     * The source model for the route.
     *
     * @var \Hyde\Framework\Contracts\PageContract
     */
    protected PageContract $sourceModel;

    /**
     * The unique route key for the route.
     *
     * @var string The route key. Generally <output-directory/slug>.
     */
    protected string $routeKey;

    /** @inheritDoc */
    public function __construct(PageContract $sourceModel)
    {
        $this->sourceModel = $sourceModel;
        $this->routeKey = $this->constructRouteKey();
    }

    /** @inheritDoc */
    public function getPageType(): string
    {
        return $this->sourceModel::class;
    }

    /** @inheritDoc */
    public function getSourceModel(): PageContract
    {
        return $this->sourceModel;
    }

    /** @inheritDoc */
    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    /** @inheritDoc */
    public function getSourceFilePath(): string
    {
        return $this->sourceModel->getSourcePath();
    }

    /** @inheritDoc */
    public function getOutputFilePath(): string
    {
        return $this->sourceModel->getOutputPath();
    }

    /** @inheritDoc */
    public function getLink(string $currentPage = ''): string
    {
        return Hyde::relativeLink($this->getOutputFilePath(), $currentPage);
    }

    protected function constructRouteKey(): string
    {
        return $this->sourceModel->getCurrentPagePath();
    }

    /** @inheritDoc */
    public static function get(string $routeKey): ?static
    {
        return static::getFromKey($routeKey);
    }

    /** @inheritDoc */
    public static function getFromKey(string $routeKey): ?static
    {
        return Router::getInstance()->getRoutes()->get($routeKey);
    }

    /** @inheritDoc */
    public static function getFromSource(string $sourceFilePath): ?static
    {
        return Router::getInstance()->getRoutes()->first(function (RouteContract $route) use ($sourceFilePath) {
            return $route->getSourceFilePath() === $sourceFilePath;
        });
    }

    /** @inheritDoc */
    public static function getFromModel(PageContract $page): ?RouteContract
    {
        return $page->getRoute();
    }

    /** @inheritDoc */
    public static function all(): Collection
    {
        return Router::getInstance()->getRoutes();
    }
}
