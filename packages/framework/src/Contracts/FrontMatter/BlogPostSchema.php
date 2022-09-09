<?php

namespace Hyde\Framework\Contracts\FrontMatter;

interface BlogPostSchema
{
    public const MARKDOWN_POST_SCHEMA = [
        'title'       => 'string',
        'description' => 'string',
        'category'    => 'string',
        'date'        => 'string',
        'author' => [
            'type' => 'string|array',
            'array_values' => [
                'name'      => 'string',
                'username'  => 'string',
                'website'   => 'string|url',
            ],
        ],
        'image' => [
            'type' => 'string|array',
            'array_values' => [
                'path'        => 'string',
                'uri'         => 'string',
                'description' => 'string',
                'title'       => 'string',
                'copyright'   => 'string',
                'license'     => 'string',
                'licenseUrl'  => 'string',
                'author'      => 'string',
                'credit'      => 'string',
            ],
        ],
    ];
}
