<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Pages\Concerns\HydePage;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;

/**
 * Contains data for the current page being rendered/compiled.
 *
 * All public data here will be available in the Blade views through {@see ManagesViewData::shareViewData()}.
 *
 * @see \Hyde\Support\Facades\Render
 * @see \Hyde\Framework\Testing\Feature\RenderHelperTest
 */
class RenderData implements Arrayable
{
    protected HydePage $page;

    protected Route $route;

    protected string $routeKey;

    public function setPage(HydePage $page): void
    {
        $this->page = $page;
        $this->route = $page->getRoute();
        $this->routeKey = $page->getRouteKey();

        $this->shareToView();
    }

    public function getPage(): ?HydePage
    {
        return $this->page ?? null;
    }

    /**
     * @deprecated v1.0.0-RC.2 - Renamed to getRoute() to match renamed property.
     * @codeCoverageIgnore
     */
    #[Deprecated(reason: 'Renamed to getRoute() to match renamed property.', replacement: '%class%->getRoute()')]
    public function getCurrentRoute(): ?Route
    {
        return $this->getRoute();
    }

    public function getRoute(): ?Route
    {
        return $this->route ?? null;
    }

    /**
     * @deprecated v1.0.0-RC.2 - Renamed to getRouteKey() to match renamed property.
     * @codeCoverageIgnore
     */
    #[Deprecated(reason: 'Renamed to getRoute() to match renamed property.', replacement: '%class%->getRouteKey()')]
    public function getCurrentPage(): ?string
    {
        return $this->getRouteKey();
    }

    public function getRouteKey(): ?string
    {
        return $this->routeKey ?? null;
    }

    public function shareToView(): void
    {
        View::share($this->toArray());
    }

    public function share(string $key, mixed $value): void
    {
        if (property_exists($this, $key)) {
            $this->{$key} = $value;
            $this->shareToView();
        } else {
            throw new InvalidArgumentException("Property '$key' does not exist on ".self::class);
        }
    }

    public function clearData(): void
    {
        unset($this->page, $this->route, $this->routeKey);
        View::share(['page' => null, 'route' => null, 'routeKey' => null]);
    }

    /**
     * @return array{render: $this, page: \Hyde\Pages\Concerns\HydePage|null, currentRoute: \Hyde\Support\Models\Route|null, currentPage: string|null}
     */
    public function toArray(): array
    {
        return [
            'render' => $this,
            'page' => $this->getPage(),
            'route' => $this->getRoute(),
            'routeKey' => $this->getRouteKey(),
        ];
    }
}
