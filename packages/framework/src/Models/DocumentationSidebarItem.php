<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Models\Pages\DocumentationPage;
use Illuminate\Support\Str;

/**
 * Object containing information for a sidebar item.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarServiceTest
 * @phpstan-consistent-constructor
 */
class DocumentationSidebarItem
{
    public string $label;
    public Route $route;
    public int $priority;
    public bool $hidden = false;
    public ?string $category = null;

    public function __construct(string $label, Route $route, ?int $priority = null, ?string $category = null, bool $hidden = false)
    {
        $this->label = $label;
        $this->route = $route;
        $this->priority = $priority ?? $this->findPriorityInConfig($route);
        $this->category = $this->normalizeCategoryKey($category);
        $this->hidden = $hidden;
    }

    protected function findPriorityInConfig(string $slug): int
    {
        $orderIndexArray = config('docs.sidebar_order', []);

        if (! in_array($slug, $orderIndexArray)) {
            return 500;
        }

        return array_search($slug, $orderIndexArray) + 250;

        // Adding 250 makes so that pages with a front matter priority that is lower
        // can be shown first. It's lower than the fallback of 500 so that they
        // still come first. This is all to make it easier to mix priorities.
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public static function fromPage(DocumentationPage $page): static
    {
        return new static(
            $page->matter['label'] ?? $page->title,
            $page->getRoute(),
            $page->matter['priority'] ?? null,
            $page->category ?? null,
            $page->matter['hidden'] ?? false
        );
    }

    protected function normalizeCategoryKey(?string $category): ?string
    {
        return empty($category) ? null : Str::slug($category);
    }
}
