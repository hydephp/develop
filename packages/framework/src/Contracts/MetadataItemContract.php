<?php

namespace Hyde\Framework\Contracts;

/**
 * @deprecated 
 */
interface MetadataItemContract extends \Stringable
{
    public function uniqueKey(): string;
}
