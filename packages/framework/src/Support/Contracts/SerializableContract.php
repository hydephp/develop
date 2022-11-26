<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

interface SerializableContract extends Arrayable, Jsonable, JsonSerializable
{
    //
}
