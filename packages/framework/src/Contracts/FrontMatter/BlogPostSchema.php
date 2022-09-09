<?php

namespace Hyde\Framework\Contracts\FrontMatter;

interface BlogPostSchema
{
    public const MARKDOWN_POST_SCHEMA = [
        'title' => 'string',
        'description' => 'string',
        'category' => 'string',
        'date' => 'string',
        'author' => [
            'type' => 'string|array',
            'array_values' => [
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
        ],
    ];
}