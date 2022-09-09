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
        'title' => [
            'type' => 'string',
        ],
        'navigation' => [
            'type' => 'array',
            'properties' => [
                'title' => [
                    'type' => 'string',
                ],
                'hidden' => [
                    'type' => 'bool',
                ],
                'priority' => [
                    'type' => 'int',
                ],
            ],
        ],
        'canonicalUrl' => [
            'type' => 'string',
        ],
    ];
}
