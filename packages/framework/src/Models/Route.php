<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Concerns\AbstractPage;
use Hyde\Framework\Concerns\JsonSerializesArrayable;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Framework\Foundation\RouteCollection;
use Hyde\Framework\Hyde;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @see \Hyde\Framework\Testing\Feature\RouteTest
 */
class Route implements RouteContract, \Stringable, \JsonSerializable, Arrayable
{
    use JsonSerializesArrayable;

    /**
     * The source model for the route.
     *
     * @var \Hyde\Framework\Concerns\AbstractPage
     */
    protected AbstractPage $sourceModel;

    /**
     * The unique route key for the route.
     *
     * @var string The route key. Generally <output-directory/slug>.
     */
    protected string $routeKey;

    /** @inheritDoc */
    public function __construct(AbstractPage $sourceModel)
    {
        $this->sourceModel = $sourceModel;
        $this->routeKey = $sourceModel->getRouteKey();
    }

    /** @inheritDoc */
    public function __toString(): string
    {
        return $this->getLink();
    }

    /** @inheritDoc */
    public function toArray(): array
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
    public function getSourceModel(): AbstractPage
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

    /** @inheritDoc */
    public static function get(string $routeKey): static
    {
        return static::getFromKey($routeKey);
    }

    /** @inheritDoc */
    public static function getFromKey(string $routeKey): static
    {
        return Hyde::routes()->get($routeKey) ?? throw new RouteNotFoundException($routeKey);
    }

    /** @inheritDoc */
    public static function getFromSource(string $sourceFilePath): static
    {
        return Hyde::routes()->first(function (RouteContract $route) use ($sourceFilePath) {
            return $route->getSourceFilePath() === $sourceFilePath;
        }) ?? throw new RouteNotFoundException($sourceFilePath);
    }

    /** @inheritDoc */
    public static function getFromModel(AbstractPage $page): RouteContract
    {
        return $page->getRoute();
    }

    /** @inheritDoc */
    public static function all(): RouteCollection
    {
        return Hyde::routes();
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
        return Hyde::routes()->has($routeKey);
    }
}
