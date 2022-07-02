<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Hyde;
use Illuminate\Support\Str;
use Spatie\YamlFrontMatter\YamlFrontMatter;

/**
 * Object containing information for a sidebar item.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarServiceTest
 * @phpstan-consistent-constructor
 */
class DocumentationSidebarItem
{
    public string $label;
    public string $destination;
    public int $priority;
    public bool $hidden = false;
    public ?string $category = null;

    public function __construct(string $label, string $destination, ?int $priority = null, ?string $category = null, bool $hidden = false)
    {
        $this->label = $label;
        $this->destination = $destination;
        $this->priority = $priority ?? $this->findPriorityInConfig($destination);
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

    public static function parseFromFile(string $documentationPageSlug): static
    {
        $matter = YamlFrontMatter::markdownCompatibleParse(
            file_get_contents(Hyde::getDocumentationPagePath('/'.$documentationPageSlug.'.md'))
        )->matter();

        return new static(
            $matter['label'] ?? Hyde::makeTitle($documentationPageSlug),
            $documentationPageSlug,
            $matter['priority'] ?? null,
            $matter['category'] ?? null,
            $matter['hidden'] ?? false
        );
    }

    protected function normalizeCategoryKey(?string $category): ?string
    {
        return empty($category) ? null : Str::slug($category);
    }
}
