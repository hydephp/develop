<?php

declare(strict_types=1);

namespace Hyde\Support\Concerns;

use function json_encode;
use function collect;

/**
 * Automatically serializes an Arrayable implementation when JSON is requested.
 *
 * @see \Hyde\Support\Contracts\SerializableContract
 */
trait Serializable
{
    /** @inheritDoc */
    abstract public function toArray(): array;

    /** Recursively serialize Arrayables */
    public function arraySerialize(): array
    {
        return collect($this->toArray())->toArray();
    }

    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        return $this->arraySerialize();
    }

    /** @param  int  $options */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
