<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Support\Models\Route;
use Hyde\Support\Facades\Render;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Collection;

use function collect;

/**
 * @experimental This class may change significantly before its release.
 *
 * @todo Consider making into a service which can create the sidebar as well.
 */
class GeneratesDocumentationSidebarMenu
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    protected function __construct()
    {
        $this->items = new Collection();
    }

    public static function handle(): DocumentationSidebar
    {
        $menu = new static();

        $menu->generate();
        $menu->sortByPriority();
        $menu->removeDuplicateItems();

        return new DocumentationSidebar($menu->items);
    }

    protected function canAddRoute(Route $route): bool
    {
        return $route->getPage()->showInNavigation() && ! $route->is(DocumentationPage::homeRouteName());
    }

    protected function removeDuplicateItems(): void
    {
        $this->items = $this->items->unique(function (NavItem $item): string {
            // Filter using a combination of the group and label to allow duplicate labels in different groups
            return $item->getGroup().$item->label;
        });
    }

    protected function sortByPriority(): void
    {
        $this->items = $this->items->sortBy('priority')->values();
    }

    private function isPageIndexPage(): bool
    {
        return Render::getPage()->getRoute()->is(DocumentationPage::homeRouteName());
    }

    private function shouldIndexPageBeActive(string $group): bool
    {
        return Render::getPage()->navigationMenuGroup() === 'other' && $group === collect($this->getGroups())->first();
    }
}
