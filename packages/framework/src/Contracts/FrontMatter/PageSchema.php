<?php
declare(strict_types=1);

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
