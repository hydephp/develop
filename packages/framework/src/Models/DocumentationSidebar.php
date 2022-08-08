<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Services\RoutingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DocumentationSidebar extends NavigationMenu
{
    /** @return $this */
    public function generate(): static
    {
        RoutingService::getInstance()->getRoutesForModel(DocumentationPage::class)->each(function (Route $route) {
            if (! $route->getSourceModel()->get('hidden', false)) {
                $this->items->push(NavItem::fromRoute($route)->setPriority($this->getPriorityForRoute($route)));
            }
        });

        return $this;
    }

    public function hasGroups(): bool
    {
       return count($this->getGroups()) > 1;
    }

    public function getGroups(): array
    {
        return $this->items->map(function (NavItem $item) {
            return $item->getGroup();
        })->unique()->toArray();
    }

    public function getItemsInGroup(?string $group): Collection
    {
        return $this->items->filter(function ($item) use ($group) {
            return $item->getGroup() === $group || $item->getGroup() === Str::slug($group);
        })->sortBy('priority')->values();
    }

    protected function filterHiddenItems(): Collection
    {
        return $this->items;
    }

    protected function getPriorityForRoute(Route $route): int
    {
        return $route->getSourceModel()->get('priority');
    }
}
