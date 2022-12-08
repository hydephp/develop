<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Foundation\Facades\Router;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarTest
 */
class DocumentationSidebar extends NavigationMenu
{
    /** @return $this */
    public function generate(): static
    {
        Router::getRoutes(DocumentationPage::class)->each(function (Route $route): void {
            $this->items->push(tap(NavItem::fromRoute($route)->setPriority($this->getPriorityForRoute($route)), function (NavItem $item): void {
                $item->label = $item->route->getPage()->data('navigation.label');
            }));
        });

        return $this;
    }

    public function hasGroups(): bool
    {
        return count($this->getGroups()) >= 1 && $this->getGroups() !== [0 => 'other'];
    }

    public function getGroups(): array
    {
        return $this->items->map(function (NavItem $item): string {
            return $item->getGroup();
        })->unique()->toArray();
    }

    public function getItemsInGroup(?string $group): Collection
    {
        return $this->items->filter(function ($item) use ($group): bool {
            return $item->getGroup() === $group || $item->getGroup() === Str::slug($group);
        })->sortBy('navigation.priority')->values();
    }

    protected function getPriorityForRoute(Route $route): int
    {
        return $route->getPage()->data('navigation.priority');
    }

    protected function filterDocumentationPage(NavItem $item): bool
    {
        return ! parent::filterDocumentationPage($item);
    }
}
