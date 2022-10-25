<?php

namespace Hyde\Framework\Modules\Metadata\Models;

use Hyde\Framework\Contracts\MetadataItemContract;

class MetadataItem implements MetadataItemContract
{
    public function __construct(protected string $name, protected string $content)
    {
    }

    public function __toString(): string
    {
        return '<meta name="'.e($this->name).'" content="'.e($this->content).'">';
    }

    public function uniqueKey(): string
    {
        return $this->name;
    }
}
