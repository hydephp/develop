<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
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
        return $this->items->first(function (NavigationItem|NavigationGroup $item): bool {
            return $this->legacy_isGroupActive($item->getGroupKey());
        });

        // TODO:  ?? $this->items->first(fn (NavigationItem|NavigationGroup $item): bool => $item->getLabel() === 'Other')
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
    public function legacy_isGroupActive(string $group): bool
    {
        $groupMatchesCurrentPageGroup = NavigationItem::normalizeGroupKey(Render::getPage()->navigationMenuGroup()) === $group;

        if ($this->isCurrentPageIndexPage()) {
            return $this->shouldIndexPageBeActive($group) || $groupMatchesCurrentPageGroup;
        }

        return $groupMatchesCurrentPageGroup;
    }

    private function isCurrentPageIndexPage(): bool
    {
        return Render::getPage()->getRoute()->is(DocumentationPage::homeRouteName());
    }

    private function shouldIndexPageBeActive(string $group): bool
    {
        // Unless the index page has a specific group set, the first group in the sidebar should be open when visiting the index page.

        if (filled(Render::getPage()->navigationMenuGroup())) {
            return false;
        }

        return $group === $this->getItems()->firstOrFail()->getGroupKey();
    }
}
