<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Contracts\Support\Arrayable;

use function app;
use function filled;
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

    public function getActiveGroup(): ?NavigationGroup
    {
        if (! $this->hasGroups()) {
            return null;
        }

        $currentPage = Render::getPage();

        return $this->items->first(function (NavigationGroup $item) use ($currentPage): bool {
            return $item->getGroupKey() && $this->legacy_isGroupActive($item->getGroupKey(), $currentPage);
        }) ?? $this->items->first(fn (NavigationGroup $item): bool => $item->getLabel() === 'Other');
    }

    /**
     * Is a page within the group the current page? This is used to determine if the sidebar group should be open when loading the page.
     *
     * @todo See if we can make this deterministic, so we pre-discover which group will be active, without having to weirdly check this in the view.
     *
     * @internal This method is used in the sidebar view to determine if a group should be open, and is not intended to be used in other contexts.
     *
     * For index pages, this will also return true for the first group in the menu, unless the index page has a specific group set.
     *
     * We have this logic here because not all NavigationItem instances belong to sidebars, and we need data from both.
     */
    public function isGroupActive(string $group): bool
    {
        return $group === $this->getActiveGroup()?->getGroupKey();
    }

    /** @deprecated Temporary method to aid in refactoring. */
    protected function legacy_isGroupActive(string $group, HydePage $currentPage): bool
    {
        if ($this->isCurrentPageIndexPage($currentPage)) {
            return $this->shouldIndexPageBeActive($group, $currentPage);
        }

        return $this->groupMatchesCurrentPageGroup($currentPage, $group);
    }

    private function isCurrentPageIndexPage(HydePage $currentPage): bool
    {
        return $currentPage->getRoute()->is(DocumentationPage::homeRouteName());
    }

    private function shouldIndexPageBeActive(string $group, HydePage $currentPage): bool
    {
        // Unless the index page has a specific group set, the first group in the sidebar should be open when visiting the index page.

        if (filled($currentPage->navigationMenuGroup())) {
            return false;
        }

        return ($group === $this->getItems()->firstOrFail()->getGroupKey()) || $this->groupMatchesCurrentPageGroup($currentPage, $group);
    }

    protected function groupMatchesCurrentPageGroup(HydePage $currentPage, string $group): bool
    {
        return NavigationItem::normalizeGroupKey($currentPage->navigationMenuGroup()) === $group;
    }
}
