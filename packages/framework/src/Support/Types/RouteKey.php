<?php

declare(strict_types=1);

namespace Hyde\Support\Types;

use Stringable;

/**
 * Route Keys are the core of Hyde's routing system.
 *
 * The route key is generally <output-directory/slug>.
 *
 * @example ```php
    // Source file: _pages/index.md/index.blade.php
    // Compiled file: _site/index.html
    Route::get('index')

    // Source file: _posts/my-post.md
    // Compiled file: _site/posts/my-post.html
    Route::get('posts/my-post')

    // Source file: _docs/readme.md
    // Compiled file: _site/docs/readme.html
    Route::get('docs/readme')
 * ```
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
