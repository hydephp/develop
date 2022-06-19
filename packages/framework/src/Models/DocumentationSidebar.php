<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\DocumentationSidebarContract;
use Illuminate\Support\Collection;

/**
 * The documentation sidebar, containing all the sidebar items.
 *
 * Extends the \Illuminate\Support\Collection class and has helper
 * methods to fluently add DocumentationSidebarItems to the
 * collection using method chaining.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarServiceTest
 */
class DocumentationSidebar extends Collection implements DocumentationSidebarContract
{
    public function addItem(DocumentationSidebarItem $item): self
    {
        $this->push($item);

        return $this;
    }

    public function sortItems(): self
    {
        return $this->sortBy('priority')
            ->values(); // Reset the keys to consecutively numbered indexes:
    }

    public function getCollection(): self
    {
        return $this;
    }
}
