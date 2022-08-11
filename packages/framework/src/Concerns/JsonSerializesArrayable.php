<?php

namespace Hyde\Framework\Concerns;

trait JsonSerializesArrayable
{
    /** @inheritDoc */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /** @inheritDoc */
    abstract public function toArray();
}
