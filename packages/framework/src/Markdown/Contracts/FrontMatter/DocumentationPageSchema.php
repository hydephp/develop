<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

/**
 * @see \Hyde\Pages\DocumentationPage
 * @deprecated The navigation is inherited by the parent page, and the category is not documented, thus presumed to be unused.
 */
interface DocumentationPageSchema
{
    public const DOCUMENTATION_PAGE_SCHEMA = [
        'category'  => 'string',
        'navigation'    => 'array|navigation',
    ];
}
