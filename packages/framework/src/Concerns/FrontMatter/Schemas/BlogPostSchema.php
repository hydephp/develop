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
     * @yamlType string|array|optional
     *
     * @example "image.jpg" # Expanded by Hyde to `_media/image.jpg` and is resolved automatically
     * @example "https://cdn.example.com/image.jpg" # Full URL starting with `http(s)://`)
     * @example ```yaml
     * image:
     *   path: image.jpg
     *   uri: https://cdn.example.com/image.jpg # Takes precedence over `path`
     *   description: 'Alt text for image'
     *   title: 'Tooltip title'
     *   copyright: 'Copyright (c) 2022'
     *   license: 'CC-BY-SA-4.0'
     *   licenseUrl: https://example.com/license/
     *   credit: https://photographer.example.com/
     *   author: 'John Doe'
     * ```
     */
    public ?Image $image = null;

    protected function constructBlogPostSchema(): void
    {
        $this->category = $this->matter('category');
        $this->description = $this->matter('description', $this->makeDescription());
        $this->date = $this->matter('date') !== null ? new DateString($this->matter('date')) : null;
        $this->author = $this->getAuthor();
        $this->image = $this->getImage();
    }

    protected function makeDescription(): string
    {
        if (strlen($this->markdown) >= 128) {
            return substr($this->markdown, 0, 125).'...';
        }

        return $this->markdown;
    }

    protected function getAuthor(): ?Author
    {
        if ($this->matter('author')) {
            return Author::make($this->matter('author'));
        }

        return null;
    }

    protected function getImage(): ?Image
    {
        if ($this->matter('image')) {
            return Image::make($this->matter('image'));
        }

        return null;
    }
}
