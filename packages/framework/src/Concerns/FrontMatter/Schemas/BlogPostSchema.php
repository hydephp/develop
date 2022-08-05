<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Models\Author;
use Hyde\Framework\Models\DateString;
use Hyde\Framework\Models\Image;

trait BlogPostSchema
{
    /** @example "My New Post" */
    public string $title;

    /** @example "A short description" */
    public ?string $description = null;

    /** @example "general", "my favorite recipes" */
    public ?string $category = null;

    /**
     * The date the post was published.
     *
     * @example 'YYYY-MM-DD [HH:MM]' (Must be parsable by `strtotime()`)
     * @yamlType string|optional
     */
    public ?DateString $date = null;

    /**
     * @example See author section
     * @yamlType string|array|optional
     */
    public ?Author $author = null;

    /**
     * @example See image section
     * @yamlType string|array|optional
     */
    public ?Image $image = null;

    protected function constructBlogPostSchema(): void
    {
        $this->category = $this->matter('category');
    }
}
