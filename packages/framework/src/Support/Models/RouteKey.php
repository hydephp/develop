<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Stringable;

/**
 * Route keys are the core of Hyde's routing system.
 *
 * In short, the route key is the URL path relative to the site root.
 *
 * For example, `_pages/index.blade.php` would be compiled to `_site/index.html` and thus has the route key of `index`.
 * As another example, `_posts/welcome.md` would be compiled to `_site/posts/welcome.html` and thus has the route key of `posts/welcome`.
 */
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
