<?php

namespace Hyde\Framework\Models\Metadata;

use Hyde\Framework\Contracts\AbstractPage;

class Metadata
{
    protected AbstractPage $page;

    public array $links = [];
    public array $metadata = [];
    public array $properties = [];

    public function __construct(AbstractPage $page)
    {
        $this->page = $page;
        $this->generate();
    }

    public function render(): string
    {
        return implode("\n", array_merge(
            $this->links,
            $this->metadata,
            $this->properties
        ));
    }

    protected function generate(): void
    {
        //
    }
}
