<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * @template TKey of array-key
 * @template TValue
 */
interface SerializableContract extends Arrayable, Jsonable, JsonSerializable
{
    /**
     * Get the instance as an array.
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array;

    /**
     * Convert the instance to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0): string;

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize(): mixed;
}
