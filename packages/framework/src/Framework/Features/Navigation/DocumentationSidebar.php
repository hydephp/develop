<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Foundation\Facades\Routes;
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
    /** @deprecated Will be made protected */
    public function generate(): static
    {
        Routes::getRoutes(DocumentationPage::class)->each(function (Route $route): void {
            if (static::canAddRoute($route)) {
                $this->items->put($route->getRouteKey(), SidebarItem::fromRoute($route));
            }
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
        return $this->items->map(function (SidebarItem $item): string {
            return $item->getGroup();
        })->unique()->toArray();
    }

    /** @return Collection<\Hyde\Framework\Features\Navigation\SidebarItem> */
    public function getItemsInGroup(?string $group): Collection
    {
        return $this->items->filter(function (SidebarItem $item) use ($group): bool {
            return ($item->getGroup() === $group) || ($item->getGroup() === Str::slug($group));
        })->sortBy('navigation.priority')->values();
    }

    public function isGroupActive(string $group): bool
    {
        return Str::slug(Render::getPage()->navigationMenuGroup()) === $group
            || $this->isPageIndexPage() && $this->shouldIndexPageBeActive($group);
    }

    /** @deprecated Hidden items should not be added to start with */
    protected static function shouldItemBeHidden(NavItem $item): bool
    {
        return parent::shouldItemBeHidden($item) || $item->getRoute()?->is(DocumentationPage::homeRouteName());
    }

    protected static function canAddRoute(Route $route): bool
    {
        return parent::canAddRoute($route) && ! $route->is(DocumentationPage::homeRouteName());
    }

    private function isPageIndexPage(): bool
    {
        return Render::getPage()->getRoute()->is(DocumentationPage::homeRouteName());
    }

    private function shouldIndexPageBeActive(string $group): bool
    {
        return Render::getPage()->navigationMenuGroup() === 'other' && $group === collect($this->getGroups())->first();
    }
}
