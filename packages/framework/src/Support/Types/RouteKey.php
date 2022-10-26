<?php

declare(strict_types=1);

namespace Hyde\Support\Types;

use Stringable;

final class RouteKey implements Stringable
{
    private string $key;

    public static function make(string $key): self
    {
        return new self($key);
    }

    public function __construct(string $key) {
        $this->key = $key;
    }

    public function __toString()
    {
        return $this->key;
    }

    public function get(): string
    {
        return $this->key;
    }
}
