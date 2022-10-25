<?php

namespace Hyde\Framework\Modules\Metadata\Models;

class OpenGraphElement extends BaseMetadataElement
{
    protected string $property;
    protected string $content;

    public function __construct(string $property, string $content)
    {
        $this->property = $this->normalizeProperty($property);
        $this->content = $content;
    }

    public function __toString(): string
    {
        return '<meta property="og:'.e($this->property).'" content="'.e($this->content).'">';
    }

    public function uniqueKey(): string
    {
        return $this->property;
    }

    protected function normalizeProperty(string $property): string
    {
        return str_starts_with($property, 'og:') ? substr($property, 3) : $property;
    }
}
