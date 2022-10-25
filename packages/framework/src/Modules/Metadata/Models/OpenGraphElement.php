<?php

namespace Hyde\Framework\Modules\Metadata\Models;

class OpenGraphElement implements \Stringable
{
    protected string $property;
    protected string $content;

    public function __construct(string $property, string $content)
    {
        $this->property = $property;
        $this->content = $content;

        $this->normalizeProperty();
    }

    public function __toString(): string
    {
        return '<meta property="'.e($this->property).'" content="'.e($this->content).'">';
    }

    public function uniqueKey(): string
    {
        return substr($this->property, 3);
    }

    protected function normalizeProperty(): void
    {
        $this->property = str_starts_with($this->property, 'og:') ? $this->property : 'og:'.$this->property;
    }
}
