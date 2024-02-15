<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Collection;

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
}
