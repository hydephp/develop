<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\AuthorSchema;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;

/**
 * @see \Hyde\Pages\MarkdownPost
 */
interface BlogPostSchema extends PageSchema
{
    public const BLOG_POST_SCHEMA = [
        'title' => 'string',
        'description' => 'string',
        'category' => 'string',
        'date' => 'string',
        'author' => ['string', AuthorSchema::AUTHOR_SCHEMA],
        'image' => ['string', FeaturedImageSchema::FEATURED_IMAGE_SCHEMA],
    ];
}
