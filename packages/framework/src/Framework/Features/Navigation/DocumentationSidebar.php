<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

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

    public function getFooter(): bool|string
    {
        return Config::get('docs.sidebar.footer', true);
    }

    public function isCollapsible(): bool
    {
        return Config::getBool('docs.sidebar.collapsible', true);
    }

    public function hasFooter(): bool
    {
        if (is_string(Config::get('docs.sidebar.footer', true))) {
            return true;
        }

        return Config::getBool('docs.sidebar.footer', true);
    }

    public function hasGroups(): bool
    {
        return $this->getItems()->contains(fn (NavItem $item): bool => $item->hasChildren());
    }

    /**
     * Is a page within the group the current page? This is used to determine if the sidebar group should be open when loading the page.
     *
     * For index pages, this will also return true for the first group in the menu, unless the index page has a specific group set.
     */
    public function isGroupActive(string $group): bool
    {
        $groupMatchesCurrentPageGroup = Str::slug(Render::getPage()->navigationMenuGroup()) === $group;
        $currentPageIsIndexPageAndShouldBeActive = $this->isCurrentPageIndexPage() && $this->shouldIndexPageBeActive($group);

        return $groupMatchesCurrentPageGroup || $currentPageIsIndexPageAndShouldBeActive;
    }

    private function isCurrentPageIndexPage(): bool
    {
        return Render::getPage()->getRoute()->is(DocumentationPage::homeRouteName());
    }

    private function shouldIndexPageBeActive(string $group): bool
    {
        // Unless the index page has a specific group set, the first group in the sidebar should be active.

        $indexPageHasNoSetGroup = Render::getPage()->navigationMenuGroup() === null;

        $firstGroupInSidebar = $this->getItems()->firstWhere(fn (NavItem $item): bool => $item->hasChildren());

        $groupIsTheFirstOneInSidebar = $group === $firstGroupInSidebar?->getIdentifier();

        return $indexPageHasNoSetGroup && $groupIsTheFirstOneInSidebar;
    }
}
