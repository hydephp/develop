<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Stringable;

final class RouteKey implements Stringable
{
    protected readonly string $key;

    public static function make(string $key): self
    {
        return new RouteKey($key);
    }

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function __toString(): string
    {
        return $this->key;
    }

    public function get(): string
    {
        return $this->key;
    }
}
