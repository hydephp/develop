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
            'description' => 'The title of the page used in the HTML <title> tag, among others.',
            'example' => '"Home", "About", "Blog Feed"',
        ],
        'navigation' => [
            'type' => 'array',
            'description' => 'The settings for how the page should be presented in the navigation menu. All array values are optional, as long as the array is not empty.',
            'example' => <<<'MARKDOWN'
```yaml
navigation:
    title: "Home"
    hidden: true
    priority: 1
```
MARKDOWN
,
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
            'description' => 'The canonical URL of the page.',
            'example' => '"https://example.com/about"',
        ],
    ];
}
