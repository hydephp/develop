<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter\SubSchemas;

/**
 * @see \Hyde\Framework\Features\Blogging\Models\LegacyFeaturedImage
 * @see \Hyde\Pages\MarkdownPost
 */
interface FeaturedImageSchema
{
    public const FEATURED_IMAGE_SCHEMA = [
        'path'           => 'string',
        'url'            => 'string',
        'description'    => 'string',
        'title'          => 'string',
        'copyright'      => 'string',
        'license'        => 'string',
        'licenseUrl'     => 'string',
        'author'         => 'string',
        'attributionUrl' => 'string',
    ];
}
