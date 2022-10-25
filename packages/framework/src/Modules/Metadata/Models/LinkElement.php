<?php

namespace Hyde\Framework\Modules\Metadata\Models;

class LinkElement extends GenericMetadataElement
{
    protected string $rel;
    protected string $href;
    protected array $attr = [];

    public function __construct(string $rel, string $href, array $attr = [])
    {
        $this->rel = $rel;
        $this->href = $href;
        $this->attr = $attr;
    }

    public function __toString(): string
    {
        return sprintf('<link rel="%s" href="%s"%s>', e($this->rel), e($this->href), $this->formatOptionalAttributes());
    }

    public function uniqueKey(): string
    {
        return $this->rel;
    }

    protected function formatOptionalAttributes(): string
    {
        if (empty($this->attr)) {
            return '';
        }

        return sprintf(' %s', collect($this->attr)->map(function ($value, $key) {
            return e($key).'="'.e($value).'"';
        })->implode(' '));
    }
}
