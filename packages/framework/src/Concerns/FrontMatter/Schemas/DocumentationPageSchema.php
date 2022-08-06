<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Illuminate\Support\Str;

trait DocumentationPageSchema
{
    /**
     * The sidebar category group, if any.
     */
    public ?string $category = null;

    /**
     * The label for the page shown in the sidebar
     */
    public ?string $label;

    /**
     * Hides the page from the sidebar.
     */
    public ?bool $hidden = false;

    /**
     * The priority of the page used for ordering the sidebar
     */
    public ?int $priority = 500;

    protected function constructDocumentationPageSchema(): void
    {
        $this->category = static::getDocumentationPageCategory();
    }

    protected function getDocumentationPageCategory(): ?string
    {
        // If the documentation page is in a subdirectory,
        // then we can use that as the category name.
        // Otherwise, we look in the front matter.

        return str_contains($this->identifier, '/')
            ? Str::before($this->identifier, '/')
            : $this->matter('category');
    }
}
