<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Pages\Concerns\HydePage;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

/**
 * Contains data for the current page being rendered/compiled.
 *
 * All public data here will be available in the Blade views through @see ManagesViewData::shareViewData().
 *
 * @see \Hyde\Support\Facades\Render
 * @see \Hyde\Framework\Testing\Feature\RenderHelperTest
 *
 * @todo Refactor to actually utilize this class
 */
class Render implements Arrayable
{
    protected HydePage $page;
    protected Route $currentRoute;
    protected string $currentPage;

    public function setPage(HydePage $page): void
    {
        $this->page = $page;
        $this->currentRoute = $page->getRoute();
        $this->currentPage = $page->getRouteKey();
    }

    public function getPage(): ?HydePage
    {
        return $this->page ?? self::handleFallback('page');
    }

    public function getCurrentRoute(): ?Route
    {
        return $this->currentRoute ?? self::handleFallback('currentRoute');
    }

    public function getCurrentPage(): ?string
    {
        return $this->currentPage ?? self::handleFallback('currentPage');
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
        unset($this->page, $this->currentRoute, $this->currentPage);
        View::share(['page' => null, 'currentRoute' => null, 'currentPage' => null]);

        // TODO: Can be used when fallback is removed: View::share($this->toArray());
    }

    public function toArray(): array
    {
        return [
            'page' => $this->getPage(),
            'currentRoute' => $this->getCurrentRoute(),
            'currentPage' => $this->getCurrentPage(),
        ];
    }

    /** @codeCoverageIgnore */
    protected static function handleFallback(string $property): mixed
    {
        $shared = View::shared($property);

        if ($shared !== null) {
            trigger_error("Setting page rendering data via the view facade is deprecated. Use `Render::share('$property', \$value)` instead", E_USER_ERROR);
        }

        return $shared;
    }
}
