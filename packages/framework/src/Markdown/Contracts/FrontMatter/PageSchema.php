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
        'title' => 'string',
        'description' => 'string', // For per page <meta name='description'> tag values. It is only used as an accessor method for the front matter, not a property.
        'canonicalUrl' => 'string', // While not present in the page data as a property, it is used for the accessor method, which reads this value from the front matter.
        'navigation' => NavigationSchema::NAVIGATION_SCHEMA,
    ];
}
