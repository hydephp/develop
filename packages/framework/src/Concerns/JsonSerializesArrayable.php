<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

/**
 * Automatically serializes an Arrayable interface when JSON is requested.
 *
 * @see \JsonSerializable
 * @see \Illuminate\Contracts\Support\Arrayable
 * @see \Hyde\Framework\Testing\Unit\JsonSerializesArrayableTest
 */
trait JsonSerializesArrayable
{
    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /** @inheritDoc */
    abstract public function toArray(): array;
}
