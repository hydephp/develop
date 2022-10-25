<?php

namespace Hyde\Framework\Modules\Metadata\Metadata;

use Hyde\Framework\Contracts\MetadataItemContract;

class LinkItem implements MetadataItemContract
{
    public function __construct(protected string $rel, protected string $href, protected array $attr = [])
    {
    }

    public function __toString(): string
    {
        if (empty($this->attr)) {
            return '<link rel="'.e($this->rel).'" href="'.e($this->href).'">';
        }

        $attributes = collect($this->attr)->map(function ($value, $key) {
            return e($key).'="'.e($value).'"';
        })->implode(' ');

        return '<link rel="'.e($this->rel).'" href="'.e($this->href).'" '.$attributes.'>';
    }

    public function uniqueKey(): string
    {
        return $this->rel;
    }
}
