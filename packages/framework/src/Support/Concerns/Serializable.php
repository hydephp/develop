<?php

declare(strict_types=1);

namespace Hyde\Support\Concerns;

/**
 * Automatically serializes an Arrayable interface when JSON is requested.
 *
 * @see \Hyde\Support\Contracts\SerializableContract
 * @see \Hyde\Framework\Testing\Unit\Serializable
 */
trait Serializable
{
    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /** @inheritDoc */
    abstract public function toArray(): array;
}
