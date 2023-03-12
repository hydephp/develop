<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;

/**
 * @see \Hyde\Pages\Concerns\HydePage
 */
interface PageSchema extends FrontMatterSchema
{
    public const PAGE_SCHEMA = [
        'title'         => 'string',
        'canonicalUrl'  => 'string', // While not present in the page data, it is supported as a front matter key
        'navigation'    => NavigationSchema::NAVIGATION_SCHEMA,
    ];
}
