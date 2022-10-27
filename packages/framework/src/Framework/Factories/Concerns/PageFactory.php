<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

use Illuminate\Contracts\Support\Arrayable;

abstract class PageFactory implements Arrayable
{
    abstract public function toArray(): array;
}
