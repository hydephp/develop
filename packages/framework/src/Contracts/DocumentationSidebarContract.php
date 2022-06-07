<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Models\DocumentationSidebarItem;

interface DocumentationSidebarContract
{
    /**
     * Add a DocumentationSidebarItem to the sidebar collection.
     */
    public function addItem(DocumentationSidebarItem $item): self;

    /**
     * Sort the items in the collection by their priority.
     */
    public function sortItems(): self;

    /**
     * Get the collection instance.
     */
    public function getCollection(): self;
}
