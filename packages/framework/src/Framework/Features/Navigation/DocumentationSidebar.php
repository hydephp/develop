<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function collect;

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
        return $this->getItems()->filter(function (NavItem $item): bool {
            return $item->hasChildren();
        })->isNotEmpty();
    }

    /**
     * @deprecated Use children instead
     *
     * @return array<string>
     */
    public function getGroups(): array
    {
        return $this->getItems()->filter(function (NavItem $item): bool {
            return $item->hasChildren();
        })->sortBy(function (NavItem $item): int {
            // Sort by lowest priority found in each group
            return collect($item->getChildren())->min(
                fn (NavItem $child): int => $child->getPriority()
            );
        })->map(function (NavItem $item): string {
            return $item->getIdentifier();
        })->values()->toArray();
    }

    /** @return Collection<\Hyde\Framework\Features\Navigation\NavItem> */
    public function getItemsInGroup(?string $group): Collection
    {
        // Todo might not need collections here
        return collect($this->getItems()->first(function (NavItem $item) use ($group): bool {
            return $item->getIdentifier() === Str::slug($group);
        })?->getChildren() ?? []);
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

    /**
     * @deprecated With the new NavItem system this should not be necessary, as the parent has a title
     *
     * @todo Get title from instance
     */
    public function makeGroupTitle(?string $group): string
    {
        return Config::getNullableString("docs.sidebar_group_labels.$group") ?? Hyde::makeTitle($group ?? 'Other');
    }

    private function isPageIndexPage(): bool
    {
        return Render::getPage()->getRoute()->is(DocumentationPage::homeRouteName());
    }

    private function shouldIndexPageBeActive(string $group): bool
    {
        $indexPageHasNoSetGroup = Render::getPage()->navigationMenuGroup() === null;
        $groupIsTheFirstOneInSidebar = $group === collect($this->getGroups())->first();

        return $indexPageHasNoSetGroup && $groupIsTheFirstOneInSidebar;
    }
}
