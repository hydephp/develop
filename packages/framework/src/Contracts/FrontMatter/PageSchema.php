<?php

namespace Hyde\Framework\Contracts\FrontMatter;

/**
 * @see \Hyde\Framework\Concerns\HydePage
 */
interface PageSchema extends Support\NavigationSchema
{
    public const PAGE_SCHEMA = [
        'title'         => 'string',
        'canonicalUrl'  => 'string|url',
        'navigation'    => 'array|navigation',
    ];
}
