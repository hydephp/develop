<?php

namespace Hyde\Framework\Modules\Metadata\Models;

class MetadataElement extends GenericMetadataElement
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
