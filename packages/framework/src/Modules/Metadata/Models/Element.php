<?php

namespace Hyde\Framework\Modules\Metadata\Models;

abstract class Element implements \Stringable
{
    abstract public function __toString(): string;

    abstract public function uniqueKey(): string;
}
