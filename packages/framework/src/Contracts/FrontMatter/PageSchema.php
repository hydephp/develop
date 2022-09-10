<?php

namespace Hyde\Framework\Contracts\FrontMatter;

/**
 * The front matter properties supported by the following HydePHP page types and their children.
 *
 * @see \Hyde\Framework\Concerns\HydePage
 */
interface PageSchema
{
    public const PAGE_SCHEMA = [
        'title'         => 'string',
        'canonicalUrl'  => 'string|url',
        'navigation'    => 'array|navigation',
    ];

    public const NAVIGATION_SCHEMA = [
        'title'     => 'string',
        'hidden'    => 'bool',
        'priority'  => 'int',
    ];
}
