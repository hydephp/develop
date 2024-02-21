<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * @experimental This class may change significantly before its release.
 *
 * @see \Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu
 */
class GeneratesDocumentationSidebarMenu extends BaseMenuGenerator
{
    public static function handle(string $menuType = DocumentationSidebar::class): DocumentationSidebar
    {
        return BaseMenuGenerator::handle(DocumentationSidebar::class);
    }
}
