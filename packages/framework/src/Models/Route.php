<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Contracts\RouteFacadeContract;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\RoutingService;
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
    public function getLink(): string
    {
        return Hyde::relativeLink($this->getOutputFilePath());
    }

    /** @todo add to contract */
    public function getPermalink(): string
    {
        return Hyde::uriPath($this->getOutputFilePath());
    }

    /** @inheritDoc */
    public function __toString(): string
    {
        return $this->getLink();
    }

    protected function constructRouteKey(): string
    {
        return $this->sourceModel->getCurrentPagePath();
    }

    /** @inheritDoc */
    public static function get(string $routeKey): static
    {
        return static::getFromKey($routeKey);
    }

    /** @inheritDoc */
    public static function getFromKey(string $routeKey): static
    {
        return RoutingService::getInstance()->getRoutes()->get($routeKey) ?? throw new RouteNotFoundException($routeKey);
    }

    /** @inheritDoc */
    public static function getFromSource(string $sourceFilePath): static
    {
        return RoutingService::getInstance()->getRoutes()->first(function (RouteContract $route) use ($sourceFilePath) {
            return $route->getSourceFilePath() === $sourceFilePath;
        }) ?? throw new RouteNotFoundException($sourceFilePath);
    }

    /** @inheritDoc */
    public static function getFromModel(PageContract $page): RouteContract
    {
        return $page->getRoute();
    }

    /** @inheritDoc */
    public static function all(): Collection
    {
        return RoutingService::getInstance()->getRoutes();
    }

    /** @inheritDoc */
    public static function current(): RouteContract
    {
        return Hyde::currentRoute() ?? throw new RouteNotFoundException('current');
    }

    /** @inheritDoc */
    public static function home(): RouteContract
    {
        return static::getFromKey('index');
    }
}
