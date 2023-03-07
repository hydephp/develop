<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

/**
 * @see \Hyde\Pages\Concerns\HydePage
 */
interface PageSchema
{
    public const PAGE_SCHEMA = [
        'title'         => 'string',
        'canonicalUrl'  => 'string',
        'navigation'    => 'array<navigation>',
    ];
}
