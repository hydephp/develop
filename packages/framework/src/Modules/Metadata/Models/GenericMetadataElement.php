<?php

namespace Hyde\Framework\Modules\Metadata\Models;

use Hyde\Framework\Modules\Metadata\MetadataElementContract;

class GenericMetadataElement implements MetadataElementContract
{
    private string $string;

    private function __construct(string $string)
    {
        $this->string = $string;
    }

    public function __toString(): string
    {
        return $this->string;
    }

    public function uniqueKey(): string
    {
        return md5($this->__toString());
    }
}
