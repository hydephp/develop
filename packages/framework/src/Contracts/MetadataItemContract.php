<?php

namespace Hyde\Framework\Contracts;

interface MetadataItemContract
{
    public function __toString(): string;

    public function uniqueKey(): string;
}
