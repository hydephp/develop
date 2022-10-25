<?php

namespace Hyde\Framework\Modules\Metadata;

interface MetadataElementContract extends \Stringable
{
    public function __toString(): string;

    public function uniqueKey(): string;
}
