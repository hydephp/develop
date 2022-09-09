<?php

namespace Hyde\Framework\Contracts\FrontMatter;

/**
 * The front matter properties supported by the following HydePHP page types and their children:
 *
 * @see \Hyde\Framework\Concerns\AbstractPage
 */
interface PageSchema
{
    public const PAGE_SCHEMA = [
        'title' => 'string',
        'navigation' => 'array',
        'canonicalUrl' => 'string',
    ];
}
