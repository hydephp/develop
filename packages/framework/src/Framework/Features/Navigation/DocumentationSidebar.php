<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

use function collect;

/** @deprecated Use the new NavigationMenu class instead */
class DocumentationSidebar
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    public function __construct(Arrayable|array $items = [])
    {
        $this->items = new Collection($items);
    }

    /** @return \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavItem> */
    public function getItems(): Collection
    {
        return $this->items->values();
    }

    /** @deprecated Will be moved to an action */
    public static function create(): static
    {
        return GeneratesDocumentationSidebarMenu::handle();
    }

    public function hasGroups(): bool
    {
        return (count($this->getGroups()) >= 1) && ($this->getGroups() !== [null]);
    }

    /** @return array<string> */
    public function getGroups(): array
    {
        return $this->items->map(function (NavItem $item): string {
            return $item->getGroup();
        })->unique()->toArray();
    }

    /** @return Collection<\Hyde\Framework\Features\Navigation\NavItem> */
    public function getItemsInGroup(?string $group): Collection
    {
        return $this->items->filter(function (NavItem $item) use ($group): bool {
            // Todo: Use identifier instead of slug
            return ($item->getGroup() === $group) || ($item->getGroup() === Str::slug($group));
        })->sortBy('navigation.priority')->values();
    }

    /**
     * Is a page within the group the current page?
     *
     * For index pages, this will also return true for the first group in the menu, unless the index page has a specific group set.
     */
    public function isGroupActive(string $group): bool
    {
        $groupMatchesCurrentPageGroup = Str::slug(Render::getPage()->navigationMenuGroup()) === $group;
        $currentPageIsIndexPageAndShouldBeActive = $this->isPageIndexPage() && $this->shouldIndexPageBeActive($group);

        return $groupMatchesCurrentPageGroup || $currentPageIsIndexPageAndShouldBeActive;
    }

    /**
     * @deprecated With the new NavItem system this should not be necessary, as the parent has a title
     *
     * @todo Get title from instance
     */
    public function makeGroupTitle(string $group): string
    {
        return Config::getNullableString("docs.sidebar_group_labels.$group") ?? Hyde::makeTitle($group);
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
