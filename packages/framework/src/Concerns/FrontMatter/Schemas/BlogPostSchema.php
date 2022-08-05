<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Models\Author;
use Hyde\Framework\Models\Image;

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
    public Author $author;

    /** @example See image section */
    public Image $image;
}
