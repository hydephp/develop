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
    /** Default implementation to dynamically serialize all public properties. Can be overridden for increased control. */
    public function toArray(): array
    {
        return $this->automaticallySerialize();
    }

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

    /** Automatically serialize all public properties. */
    protected function automaticallySerialize(): array
    {
        // Calling the function from a different scope means we only get the public properties.

        return get_object_vars(...)->__invoke($this);
    }
}
