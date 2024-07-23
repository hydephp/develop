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
        'description' => 'string', // For <meta name='description'> values. It is used by the automatic page metadata generator, which reads this value from the front matter.
        'canonicalUrl' => 'string', // While not present in the page data as a property, it is used by the accessor method, which reads this value from the front matter.
        'navigation' => NavigationSchema::NAVIGATION_SCHEMA,
    ];
}
