<?php

namespace Hyde\Framework\Contracts;

interface MetadataItemContract extends \Stringable
{
    public function uniqueKey(): string;
}
