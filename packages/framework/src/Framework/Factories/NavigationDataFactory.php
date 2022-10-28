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

    protected const FALLBACK_PRIORITY = 999;
    protected const CONFIG_OFFSET = 500;

    protected readonly ?string $label;
    protected readonly ?string $group;
    protected readonly ?bool $hidden;
    protected readonly ?int $priority;

    public function __construct()
    {
        $this->label = $this->makeLabel();
        $this->group = $this->makeGroup();
        $this->hidden = $this->makeHidden();
        $this->priority = $this->makePriority();
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'group' => $this->group,
            'hidden' => $this->hidden,
            'priority' => $this->priority,
        ];
    }

    protected function makeLabel(): ?string
    {
        //
    }

    protected function makeGroup(): ?string
    {
        //
    }

    protected function makeHidden(): ?bool
    {
        //
    }

    protected function makePriority(): ?int
    {
        //
    }
}
