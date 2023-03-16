<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Pages\Concerns\HydePage;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

use function property_exists;

/**
 * Contains data for the current page being rendered/compiled.
 *
 * All public data here will be available in the Blade views through {@see ManagesViewData::shareViewData()}.
 *
 * @see \Hyde\Support\Facades\Render
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

    public function getRoute(): ?Route
    {
        return $this->route ?? null;
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
