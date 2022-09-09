<?php

namespace Hyde\Framework\Contracts\FrontMatter;

interface BlogPostSchema
{
    public const MARKDOWN_POST_SCHEMA = [
        'title'       => 'string',
        'description' => 'string',
        'category'    => 'string',
        'date'        => 'string',
        'author'      => 'string|array|author',
        'image'       => 'string|array|featured_image',
    ];

    public const AUTHOR_SCHEMA = [
        'name'      => 'string',
        'username'  => 'string',
        'website'   => 'string|url',
    ];

    public const FEATURED_IMAGE_SCHEMA = [
        'path'         => 'string',
        'uri'          => 'string',
        'description'  => 'string',
        'title'        => 'string',
        'copyright'    => 'string',
        'license'      => 'string',
        'licenseUrl'   => 'string',
        'author'       => 'string',
        'credit'       => 'string',
    ];
}
