<?php

namespace Hyde\Framework\Contracts\FrontMatter;

/**
 * The front matter properties supported by the following HydePHP page types and their children.
 *
 * @see \Hyde\Framework\Concerns\AbstractPage
 */
interface PageSchema
{
    public const PAGE_SCHEMA = [
        'title'         => 'string',
        'navigation'    => 'array|navigation',
        'canonicalUrl'  => 'string|url',
    ];

    public const NAVIGATION_SCHEMA = [
        'title'     => 'string',
        'hidden'    => 'bool',
        'priority'  => 'int',
    ];
}
