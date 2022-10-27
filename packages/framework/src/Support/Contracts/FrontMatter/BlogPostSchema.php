<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts\FrontMatter;

/**
 * @see \Hyde\Pages\MarkdownPost
 */
interface BlogPostSchema extends \Hyde\Support\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema
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
