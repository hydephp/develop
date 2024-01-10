<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter\SubSchemas;

use Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema;

/**
 * @see \Hyde\Framework\Features\Blogging\Models\FeaturedImage
 * @see \Hyde\Pages\MarkdownPost
 */
interface FeaturedImageSchema extends BlogPostSchema
{
    public const FEATURED_IMAGE_SCHEMA = [
        'source' => 'string', // Name of a file in _media/ or a remote URL (required)
        'altText' => 'string', // The alt text (important for accessibility) // todo: Support alt, description
        'titleText' => 'string', // The title text (hover tooltip & metadata) // todo: Support title, caption
        'licenseName' => 'string', // The name of the license (e.g. "CC BY 4.0")
        'licenseUrl' => 'string', // The URL of the license (e.g. "https://creativecommons.org/licenses/by/4.0/")
        'authorName' => 'string', // The name of the author/photographer of the image (e.g. "John Doe", Wikimedia Commons)
        'authorUrl' => 'string', // The URL of the author/photographer of the image (e.g. "https://commons.wikimedia.org/wiki/User:John_Doe", Wikimedia Commons)
        'copyright' => 'string', // The copyright text (e.g. "© 2023 John Doe")
    ];
}
