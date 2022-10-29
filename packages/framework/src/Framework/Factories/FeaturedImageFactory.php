<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;

class FeaturedImageFactory extends Concerns\PageDataFactory implements FeaturedImageSchema
{
    public const SCHEMA = FeaturedImageSchema::FEATURED_IMAGE_SCHEMA;

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}
