<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

interface SerializableContract extends Arrayable, Jsonable, JsonSerializable
{
    /** @inheritDoc */
    public function toArray(): array;

    /** @inheritDoc */
    public function toJson($options = 0): string;

    /** @inheritDoc */
    public function jsonSerialize(): mixed;
}
