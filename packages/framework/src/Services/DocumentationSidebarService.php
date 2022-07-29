<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Models\DocumentationSidebar;
use Hyde\Framework\Models\DocumentationSidebarItem;
use Illuminate\Support\Str;

/**
 * Service class to create and manage the sidebar collection object.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarServiceTest
 * @phpstan-consistent-constructor
 *
 * @deprecated
 */
class DocumentationSidebarService
{
    protected array $categories = [];

    protected DocumentationSidebar $sidebar;

    public static function create(): static
    {
        return (new self)->createSidebar()->withoutIndex()->withoutHidden();
    }

    public static function get(): DocumentationSidebar
    {
        return static::create()->getSidebar()->sortItems()->getCollection();
    }

    public function createSidebar(): self
    {
        $this->sidebar = new DocumentationSidebar();

        foreach ($this->getSidebarItems() as $slug) {
            $this->sidebar->addItem(
                $this->createSidebarItemFromSlug($slug)
            );
        }

        return $this;
    }

    public function getSidebar(): DocumentationSidebar
    {
        return $this->sidebar;
    }

    public function getSortedSidebar(): DocumentationSidebar
    {
        return $this->getSidebar()->sortItems();
    }

    public function addItem(DocumentationSidebarItem $item): self
    {
        $this->sidebar->addItem($item);

        return $this;
    }

    protected function withoutIndex(): self
    {
        $this->sidebar = $this->sidebar->reject(function (DocumentationSidebarItem $item) {
            return $item->destination === 'index';
        });

        return $this;
    }

    protected function withoutHidden(): self
    {
        $this->sidebar = $this->sidebar->reject(function (DocumentationSidebarItem $item) {
            return $item->isHidden();
        });

        return $this;
    }

    protected function getSidebarItems(): array
    {
        return CollectionService::getDocumentationPageFiles();
    }

    protected function createSidebarItemFromSlug(string $slug): DocumentationSidebarItem
    {
        return DocumentationSidebarItem::parseFromFile($slug);
    }

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
