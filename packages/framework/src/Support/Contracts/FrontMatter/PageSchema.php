<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts\FrontMatter;

/**
 * @see \Hyde\Framework\Concerns\HydePage
 */
interface PageSchema extends \Hyde\Support\Contracts\FrontMatter\SubSchemas\NavigationSchema
{
    public const PAGE_SCHEMA = [
        'title'         => 'string',
        'canonicalUrl'  => 'string|url',
        'navigation'    => 'array|navigation',
    ];
}
