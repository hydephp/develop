<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Illuminate\Contracts\Support\Arrayable;

use function app;
use function is_string;

class DocumentationSidebar extends NavigationMenu
{
    /**
     * Get the navigation menu instance from the service container.
     */
    public static function get(): static
    {
        return app('navigation.sidebar');
    }

    public function __construct(Arrayable|array $items = [])
    {
        parent::__construct($items);
    }

    public function getHeader(): string
    {
        return Config::get('docs.sidebar.header', 'Documentation');
    }

    public function getFooter(): ?string
    {
        $option = Config::get('docs.sidebar.footer', '[Back to home page](../)');

        if (is_string($option)) {
            return $option;
        }

        return null;
    }

    public function hasFooter(): bool
    {
        return $this->getFooter() !== null;
    }

    public function isCollapsible(): bool
    {
        return Config::getBool('docs.sidebar.collapsible', true);
    }

    public function hasGroups(): bool
    {
        return $this->getItems()->contains(fn (NavigationItem|NavigationGroup $item): bool => $item instanceof NavigationGroup);
    }

    /**
     * Get the group that should be open when the sidebar is loaded.
     *
     * @internal This method offloads logic for the sidebar view, and is not intended to be used in other contexts.
     */
    public function getActiveGroup(): ?NavigationGroup
    {
        // A group is active when it contains the current page being rendered

        if ($this->items->isEmpty() || (! $this->hasGroups()) || (! $this->isCollapsible())) {
            return null;
        }

        $currentPage = Render::getPage();

        if ($currentPage === null) {
            return null;
        }

        if ($this->isCurrentPageIndexPage($currentPage) && blank($currentPage->navigationMenuGroup())) {
            return $this->items->sortBy(fn (NavigationGroup $item): int => $item->getPriority())->first();
        }

        return $this->items->first(function (NavigationGroup $item) use ($currentPage): bool {
            return $item->getGroupKey() === NavigationItem::normalizeGroupKey($currentPage->navigationMenuGroup());
        }) ?? $this->items->first(fn (NavigationGroup $item): bool => $item->getLabel() === 'Other');
    }

    private function isCurrentPageIndexPage(HydePage $currentPage): bool
    {
        return $currentPage->getRoute()->is(DocumentationPage::homeRouteName());
    }
}
