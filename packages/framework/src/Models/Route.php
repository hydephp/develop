<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Concerns\JsonSerializesArrayable;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Contracts\RouteFacadeContract;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\RoutingService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\RouteTest
 */
class Route implements RouteContract, RouteFacadeContract, \Stringable, \JsonSerializable, Arrayable
{
    use JsonSerializesArrayable;

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
    public function __toString(): string
    {
        return $this->getLink();
    }

    /** @inheritDoc */
    public function toArray()
    {
        return [
            'routeKey' => $this->routeKey,
            'sourceModelPath' => $this->sourceModel->getSourcePath(),
            'sourceModelType' => $this->sourceModel::class,
        ];
    }

    /** @inheritDoc */
    public function getLink(): string
    {
        return Hyde::relativeLink($this->getOutputFilePath());
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
    public function getQualifiedUrl(): string
    {
        return Hyde::url($this->getOutputFilePath());
    }

    /** @deprecated Use the route key property */
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
    public static function all(): \Hyde\Framework\RouteCollection
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

    /** @inheritDoc */
    public static function exists(string $routeKey): bool
    {
        return RoutingService::getInstance()->getRoutes()->has($routeKey);
    }
}
