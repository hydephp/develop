<?php

declare(strict_types=1);

namespace Hyde\Support\Types;

use Stringable;

/**
 * Route Keys are the core of Hyde's routing system.
 *
 * The route key is generally <output-directory/slug>.
 */
final class RouteKey implements Stringable
{
    private readonly string $key;

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
