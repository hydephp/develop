<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * Specifies that a class can be serialized to an array and/or JSON.
 *
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
     * @return array<TKey, TValue>
     */
    public function jsonSerialize(): array;
}
