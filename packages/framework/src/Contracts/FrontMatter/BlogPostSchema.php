<?php

namespace Hyde\Framework\Contracts\FrontMatter;

interface BlogPostSchema
{
    public const MARKDOWN_POST_SCHEMA = [
        'title' => [
            'type' => 'string',
            'description' => 'The title of the blog post. Same as the HTML page title.',
            'example' => '"My First Blog Post"',
        ],
        'description' => [
            'type' => 'string',
            'description' => 'The description of the post, used in meta tags and excerpts.',
            'example' => '"A short description used in previews and SEO"',
        ],
        'category' => [
            'type' => 'string',
            'description' => 'The category of the post.',
            'example' => '"News", "Updates", "Announcements"',
        ],
        'date' => [
            'type' => 'string',
            'description' => 'The publish date of the post. Must be parsable by PHP\'s `strtotime()` function.',
            'example' => ['"YYYY-MM-DD [HH:MM]"', '"Jan 1, 2022"'],
        ],
        'author' => [
            'type' => 'string|array',
            'description' => 'The author of the post. Can either be a simple name, username corresponding to a config defined author, or an array of author properties.',
            'example' => ['"John Doe"', '"john_doe"', "```yaml\nauthor:\n  name: \"John Doe\"\n  username: john_doe\n  website: https://twitter.com/HydeFramework\n```"],
            'properties' => [
                'name' => [
                    'type' => 'string',
                ],
                'username' => [
                    'type' => 'string',
                ],
                'website' => [
                    'type' => 'string|url',
                ]
            ],
        ],
        'image' => [
            'type' => 'string|array',
            'description' => 'The featured cover image of the post. When supplying an array, all values are optional, but you need either an image path or URI.',
            'example' => ['"https://example.com/image.jpg"', '"image.jpg"', "```yaml\n  image:\n  path: image.jpg\n  uri: https://cdn.example.com/image.jpg # Takes precedence over `path`\n  description: 'Alt text for image'\n  title: 'Tooltip title'\n  copyright: 'Copyright (c) 2022'\n  license: 'CC-BY-SA-4.0'\n  licenseUrl: https://example.com/license/\n  credit: https://photographer.example.com/\n  author: 'John Doe'\n```"],
        ],
    ];
}