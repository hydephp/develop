<?php

namespace Hyde\Framework\Modules\Routing;

use Hyde\Framework\Contracts\PageContract;

/**
 * @see \Hyde\Framework\Testing\Feature\RouteTest
 */
class Route implements RouteContract
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
        return class_basename($this->sourceModel);
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
    public static function get(string $routeKey): ?RouteContract
    {
        return Router::getInstance()->getRoutes()->get($routeKey);
    }

    /** @inheritDoc */
    public static function getOrFail(string $routeKey): RouteContract
    {
        return static::get($routeKey) ?? throw new RouteNotFoundException($routeKey);
    }

    protected function constructRouteKey(): string
    {
        return $this->sourceModel->getCurrentPagePath();
    }
}
