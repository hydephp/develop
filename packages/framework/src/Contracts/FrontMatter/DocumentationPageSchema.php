<?php

namespace Hyde\Framework\Contracts\FrontMatter;

/**
 * The front matter properties supported by the following HydePHP page types and their children.
 *
 * @see \Hyde\Framework\Models\Pages\DocumentationPage
 */
interface DocumentationPageSchema
{
    public const DOCUMENTATION_PAGE_SCHEMA = [
        'category'  => 'string',
        'navigation'    => 'array|navigation',
    ];
}
