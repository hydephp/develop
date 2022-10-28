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

    protected readonly ?string $label;
    protected readonly ?string $group;
    protected readonly ?bool $hidden;
    protected readonly ?int $priority;

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}
