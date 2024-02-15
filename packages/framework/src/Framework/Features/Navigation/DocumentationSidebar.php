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
        return (count($this->getGroups()) >= 1) && ($this->getGroups() !== ['other']);
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
            return ($item->getGroup() === $group) || ($item->getGroup() === Str::slug($group));
        })->sortBy('navigation.priority')->values();
    }

    public function isGroupActive(string $group): bool
    {
        return Str::slug(Render::getPage()->navigationMenuGroup()) === $group
            || $this->isPageIndexPage() && $this->shouldIndexPageBeActive($group);
    }

    public function makeGroupTitle(string $group): string
    {
        return Config::getNullableString("docs.sidebar_group_labels.$group") ?? Hyde::makeTitle($group);
    }

    /** @deprecated Move to new action */
    private function isPageIndexPage(): bool
    {
        return Render::getPage()->getRoute()->is(DocumentationPage::homeRouteName());
    }

    /** @deprecated Move to new action */
    private function shouldIndexPageBeActive(string $group): bool
    {
        return Render::getPage()->navigationMenuGroup() === 'other' && $group === collect($this->getGroups())->first();
    }
}
