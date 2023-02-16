<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Foundation\Facades\Routes;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarTest
 */
class DocumentationSidebar extends BaseNavigationMenu
{
    /** @return $this */
    public function generate(): static
    {
        Routes::getRoutes(DocumentationPage::class)->each(function (Route $route): void {
            $this->items->put($route->getRouteKey(), NavItem::fromRoute($route));
        });

        return $this;
    }

    public function hasGroups(): bool
    {
        return (count($this->getGroups()) >= 1) && ($this->getGroups() !== ['other']);
    }

    /** @return array<string> */
    public function getGroups(): array
    {
        return $this->items->map(function (NavItem $item): string {
            return $item->getGroup();
        })->unique()->toArray();
    }

    public function getItemsInGroup(?string $group): Collection
    {
        return $this->items->filter(function (NavItem $item) use ($group): bool {
            return ($item->getGroup() === $group) || ($item->getGroup() === Str::slug($group));
        })->sortBy('navigation.priority')->values();
    }

    public function isGroupActive(string $group): bool
    {
        return Render::getPage()->navigationMenuGroup() === Hyde::makeTitle($group);
    }

    protected static function shouldItemBeHidden(NavItem $item): bool
    {
        return parent::shouldItemBeHidden($item) || $item->getRoute()?->is(DocumentationPage::homeRouteName());
    }
}
