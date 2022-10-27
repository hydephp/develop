<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

class RouteKey
{
    protected readonly string $key;

    public static function make(string $key): self
    {
        return new static($key);
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
