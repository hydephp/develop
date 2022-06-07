<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Models\DocumentationSidebar;

interface DocumentationSidebarServiceContract
{
    /**
     * Generate and return a new DocumentationSidebar collection.
     */
    public static function get(): DocumentationSidebar;

    /**
     * Parse the _docs directory for sidebar items to create a new collection.
     */
    public function createSidebar(): self;

    /**
     * Get the collection of sidebar items in the class.
     */
    public function getSidebar(): DocumentationSidebar;
}
