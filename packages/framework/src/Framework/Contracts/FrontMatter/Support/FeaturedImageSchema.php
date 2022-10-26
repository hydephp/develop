<?php

declare(strict_types=1);

namespace Hyde\Framework\Contracts\FrontMatter\Support;

/**
 * @see \Hyde\Blogging\Models\FeaturedImage
 * @see \Hyde\Framework\Models\Pages\MarkdownPost
 */
interface FeaturedImageSchema
{
    public const FEATURED_IMAGE_SCHEMA = [
        'path'         => 'string',
        'url'          => 'string',
        'description'  => 'string',
        'title'        => 'string',
        'copyright'    => 'string',
        'license'      => 'string',
        'licenseUrl'   => 'string',
        'author'       => 'string',
        'credit'       => 'string',
    ];
}
