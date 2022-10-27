<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Metadata\Elements;

use Hyde\Framework\Features\Metadata\MetadataElementContract;

class MetadataElement implements MetadataElementContract
{
    protected string $name;
    protected string $content;

    public function __construct(string $name, string $content)
    {
        $this->name = $name;
        $this->content = $content;
    }

    public function __toString(): string
    {
        return sprintf('<meta name="%s" content="%s">', e($this->name), e($this->content));
    }

    public function uniqueKey(): string
    {
        return $this->name;
    }
}
