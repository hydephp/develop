<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;

/**
 * @see \Hyde\Pages\Concerns\HydePage
 */
interface PageSchema
{
    public const PAGE_SCHEMA = [
        'title'         => 'string',
        'canonicalUrl'  => 'string',
        'navigation'    => NavigationSchema::NAVIGATION_SCHEMA,
    ];
}
