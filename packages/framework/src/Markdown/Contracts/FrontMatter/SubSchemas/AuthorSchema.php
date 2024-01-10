<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter\SubSchemas;

use Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema;

/**
 * @see \Hyde\Pages\MarkdownPost
 */
interface AuthorSchema extends BlogPostSchema
{
    public const AUTHOR_SCHEMA = [
        'name' => 'string',
        'username' => 'string',
        'website' => 'string',
    ];
}
