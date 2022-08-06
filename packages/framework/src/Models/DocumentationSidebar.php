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
            if (! $route->getSourceModel()->matter('hidden', false)) {
                $this->items->push(NavItem::fromRoute($route)->setPriority($this->getPriorityForRoute($route)));
            }
        });

        return $this;
    }

    public function hasGroups(): bool
    {
        return $this->items->map(function (NavItem $item) {
            return $item->getGroup() !== null;
        })->contains(true);
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
        return $route->getSourceModel()->matter('priority') ?? $this->findPriorityInConfig($route->getSourceModel()->identifier);
    }

    protected function findPriorityInConfig(string $slug): int
    {
        $orderIndexArray = config('docs.sidebar_order', []);

        if (! in_array($slug, $orderIndexArray)) {
            return 500;
        }

        return array_search($slug, $orderIndexArray) + 250;

        // Adding 250 makes so that pages with a front matter priority that is lower
        // can be shown first. It's lower than the fallback of 500 so that they
        // still come first. This is all to make it easier to mix priorities.
    }
}
