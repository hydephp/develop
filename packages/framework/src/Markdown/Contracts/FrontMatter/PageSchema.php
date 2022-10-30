<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

/**
 * @see \Hyde\Pages\Concerns\HydePage
 */
interface PageSchema extends \Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema
{
    public const PAGE_SCHEMA = [
        'title'         => 'string',
        'canonicalUrl'  => 'string|url',
        'navigation'    => 'array|navigation',
    ];
}
