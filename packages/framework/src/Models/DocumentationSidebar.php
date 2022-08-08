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
                $this->items->push(tap(NavItem::fromRoute($route)->setPriority($this->getPriorityForRoute($route)), function (NavItem $item) {
                    $item->title =  $item->route->getSourceModel()->get('label');
                }));
            }
        });

        return $this;
    }

    public function hasGroups(): bool
    {
        return count($this->getGroups()) >= 1 && $this->getGroups() !== [0 => 'other'];
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
