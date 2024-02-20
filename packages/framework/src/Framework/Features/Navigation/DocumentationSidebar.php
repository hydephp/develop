<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** @deprecated Use the new NavigationMenu class instead */
class DocumentationSidebar extends NavigationMenu
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    /** @deprecated Will be moved to an action */
    public static function create(): static
    {
        return GeneratesDocumentationSidebarMenu::handle();
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
    public function isGroupActive(?string $group): bool
    {
        if ($group === null) {
            return false;
        }

        $groupMatchesCurrentPageGroup = Str::slug(Render::getPage()->navigationMenuGroup()) === $group;
        $currentPageIsIndexPageAndShouldBeActive = $this->isPageIndexPage() && $this->shouldIndexPageBeActive($group);

        return $groupMatchesCurrentPageGroup || $currentPageIsIndexPageAndShouldBeActive;
    }

    private function isPageIndexPage(): bool
    {
        return Render::getPage()->getRoute()->is(DocumentationPage::homeRouteName());
    }

    private function shouldIndexPageBeActive(string $group): bool
    {
        $indexPageHasNoSetGroup = Render::getPage()->navigationMenuGroup() === null;

        $firstGroupInSidebar = $this->getItems()->firstWhere(function (NavItem $item): bool {
            return $item->hasChildren();
        });

        $groupIsTheFirstOneInSidebar = $group === $firstGroupInSidebar?->getIdentifier();

        return $indexPageHasNoSetGroup && $groupIsTheFirstOneInSidebar;
    }
}
