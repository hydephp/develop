<?php

declare(strict_types=1);

namespace Hyde\Support\Concerns;

/**
 * Automatically serializes an Arrayable implementation when JSON is requested.
 *
 * @see \Hyde\Support\Contracts\SerializableContract
 * @see \Hyde\Framework\Testing\Unit\SerializableTest
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

    /** @param  int  $options */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
