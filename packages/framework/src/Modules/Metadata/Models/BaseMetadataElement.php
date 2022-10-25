<?php

namespace Hyde\Framework\Modules\Metadata\Models;

abstract class BaseMetadataElement
{
    abstract public function __toString(): string;

    abstract public function uniqueKey(): string;
}
