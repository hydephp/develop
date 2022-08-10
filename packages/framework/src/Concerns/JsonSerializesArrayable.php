<?php

namespace Hyde\Framework\Concerns;

trait JsonSerializesArrayable
{
    /** @inheritDoc */
    function jsonSerialize()
    {
        return $this->toArray();
    }
}