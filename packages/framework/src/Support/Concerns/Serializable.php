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
    abstract public function toArray(): array;

    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /** @inheritDoc */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
