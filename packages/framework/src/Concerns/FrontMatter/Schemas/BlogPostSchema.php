<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Actions\Constructors\ConfiguresFeaturedImageForPost;
use Hyde\Framework\Actions\Constructors\FindsAuthorForPost;
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
        $this->description = $this->matter('description', $this->makeDescription());
        $this->date = $this->matter('date') !== null ? new DateString($this->matter('date')) : null;
        $this->author = FindsAuthorForPost::run($this);
        $this->image = ConfiguresFeaturedImageForPost::run($this);
    }

    protected function makeDescription(): string
    {
        if (strlen($this->markdown) >= 180) {
            return substr($this->markdown, 0, 125) . '...';
        }

        return $this->markdown;
    }
}
