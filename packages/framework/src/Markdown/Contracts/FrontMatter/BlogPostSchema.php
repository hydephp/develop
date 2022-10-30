<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;

/**
 * @see \Hyde\Pages\MarkdownPost
 */
interface BlogPostSchema extends FeaturedImageSchema
{
    public const MARKDOWN_POST_SCHEMA = [
        'title'        => 'string',
        'description'  => 'string',
        'category'     => 'string',
        'date'         => 'string',
        'author'       => 'string|array|author',
        'image'        => 'string|array|featured_image',
    ];

    public const AUTHOR_SCHEMA = [
        'name'      => 'string',
        'username'  => 'string',
        'website'   => 'string|url',
    ];
}
