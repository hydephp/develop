<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Pages\DocumentationPage;

/**
 * @experimental This class may change significantly before its release.
 *
 * @see \Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu
 */
class GeneratesDocumentationSidebarMenu extends BaseMenuGenerator
{
    public static function handle(): DocumentationSidebar
    {
        $menu = new static(DocumentationSidebar::class);

        $menu->generate();

        return new DocumentationSidebar($menu->items);
    }

    protected function generate(): void
    {
        parent::generate();

        // If there are no pages other than the index page, we add it to the sidebar so that it's not empty
        if ($this->items->count() === 0 && DocumentationPage::home() !== null) {
            $this->items->push(NavItem::fromRoute(DocumentationPage::home()));
        }
    }
}
