<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;

/**
 * @see \Hyde\Pages\MarkdownPost
 */
interface BlogPostSchema extends PageSchema
{
    public const MARKDOWN_POST_SCHEMA = [
        'title'        => 'string',
        'description'  => 'string',
        'category'     => 'string',
        'date'         => 'string',
        'author'       => ['string', BlogPostSchema::AUTHOR_SCHEMA],
        'image'        => ['string', FeaturedImageSchema::FEATURED_IMAGE_SCHEMA],
    ];

    public const AUTHOR_SCHEMA = [
        'name'      => 'string',
        'username'  => 'string',
        'website'   => 'string',
    ];
}
