<?php

namespace Hyde\Framework\Contracts\FrontMatter;

/**
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
        'label'     => 'string',
        'group'     => 'string',
        'hidden'    => 'bool',
        'priority'  => 'int',
    ];
}
