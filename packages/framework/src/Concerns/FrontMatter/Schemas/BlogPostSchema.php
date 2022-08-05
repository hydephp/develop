<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

trait BlogPostSchema
{
    /** @example "My New Post" */
    public string $title;

    /** @example "A short description" */
    public ?string $description;

    /** @example "general", "my favorite recipes" */
    public ?string $category;

    /** @example "YYYY-MM-DD [HH:MM]" */
    public ?string $date;

    /** @example See author section */
    public string|array|null $author;

    /** @example See image section */
    public string|array|null $image;
}
