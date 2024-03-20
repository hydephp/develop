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
        return $this->getItems()->contains(fn (NavItem $item): bool => $item instanceof NavGroupItem);
    }

    /**
     * Is a page within the group the current page? This is used to determine if the sidebar group should be open when loading the page.
     *
     * For index pages, this will also return true for the first group in the menu, unless the index page has a specific group set.
     *
     * We have this logic here because not all NavItem instances belong to sidebars, and we need data from both.
     */
    public function isGroupActive(string $group): bool
    {
        $groupMatchesCurrentPageGroup = NavItem::normalizeGroupKey(Render::getPage()->navigationMenuGroup()) === $group;

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
        // Unless the index page has a specific group set, the first group in the sidebar should be active when on the index page.

        $indexPageHasNoSetGroup = Render::getPage()->navigationMenuGroup() === null;

        $firstGroupInSidebar = $this->getItems()->firstOrFail();

        $groupIsTheFirstOneInSidebar = $group === $firstGroupInSidebar->getGroupKey();

        return $indexPageHasNoSetGroup && $groupIsTheFirstOneInSidebar;
    }
}
