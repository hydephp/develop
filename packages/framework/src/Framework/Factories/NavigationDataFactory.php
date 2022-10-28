<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;

class NavigationDataFactory extends Concerns\PageDataFactory implements NavigationSchema
{
    /**
     * The front matter properties supported by this factory.
     *
     * Note that this represents a sub-schema, and is used as part of the page schema.
     */
    public const SCHEMA = NavigationSchema::NAVIGATION_SCHEMA;

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}
