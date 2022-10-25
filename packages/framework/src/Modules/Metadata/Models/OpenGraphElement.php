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
        return '<meta property="'.e($this->property).'" content="'.e($this->content).'">';
    }

    public function uniqueKey(): string
    {
        return substr($this->property, 3);
    }

    protected function normalizeProperty(string $property): string
    {
        return str_starts_with($property, 'og:') ? $property : 'og:'.$property;
    }
}
