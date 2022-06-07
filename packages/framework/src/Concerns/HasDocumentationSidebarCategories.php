<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Models\DocumentationSidebar;
use Illuminate\Support\Str;

/**
 * Extracts logic for the sidebar categories used in the SidebarService.
 *
 * @see \Hyde\Framework\Services\DocumentationSidebarService
 */
trait HasDocumentationSidebarCategories
{
    protected array $categories = [];

    public function hasCategories(): bool
    {
        $this->assembleCategories();

        return ! empty($this->categories);
    }

    public function getCategories(): array
    {
        $this->assembleCategories();

        return $this->categories;
    }

    public function getItemsInCategory(string $category): DocumentationSidebar
    {
        return $this->sidebar->filter(function ($item) use ($category) {
            return $item->category === Str::slug($category);
        })->sortBy('priority')->values();
    }

    protected function assembleCategories(): void
    {
        foreach ($this->sidebar->sortItems() as $item) {
            if (isset($item->category)) {
                if (! in_array($item->category, $this->categories)) {
                    $this->categories[] = $item->category;
                }
            }
        }

        if (! empty($this->categories)) {
            $this->setCategoryOfUncategorizedItems();
        }
    }

    protected function setCategoryOfUncategorizedItems(): void
    {
        foreach ($this->sidebar as $item) {
            if (! isset($item->category)) {
                $item->category = 'other';

                if (! in_array('other', $this->categories)) {
                    $this->categories[] = 'other';
                }
            }
        }
    }
}
