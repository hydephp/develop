<?php

namespace Hyde\Framework\Contracts\FrontMatter\Support;

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
