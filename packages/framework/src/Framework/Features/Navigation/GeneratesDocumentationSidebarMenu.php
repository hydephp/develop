<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * @experimental This class may change significantly before its release.
 */
class GeneratesDocumentationSidebarMenu
{
    public static function handle(): DocumentationSidebar
    {
        return BaseMenuGenerator::handle(DocumentationSidebar::class);
    }
}
