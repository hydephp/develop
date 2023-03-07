<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

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
        'author'       => 'string|array<blog_post.author>',
        'image'        => 'string|array<featured_image>',
    ];

    public const AUTHOR_SCHEMA = [
        'name'      => 'string',
        'username'  => 'string',
        'website'   => 'string',
    ];
}
