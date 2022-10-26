<?php

declare(strict_types=1);

namespace Hyde\Support\Types;

use Stringable;

/**
 * Route keys are the core of Hyde's routing system.
 *
 * IN SHORT:
 *   The route key is a string that's generally <output-directory/slug>.
 *   You can cast this object to a string, or call the get() method to get the underlying value.
 *
 * IN DETAIL:
 *
 * The route key is the URL path relative to the site root.
 *
 * For example, if the compiled page will be saved to _site/docs/index.html,
 * then this method will return 'docs/index'. Route keys are used to
 * identify pages, similar to how named routes work in Laravel.
 *
 * @example ```php
 * // Source file: _pages/index.md/index.blade.php
 * // Compiled file: _site/index.html
 * Route::get('index')
 *
 * // Source file: _posts/my-post.md
 * // Compiled file: _site/posts/my-post.html
 * Route::get('posts/my-post')
 *
 * // Source file: _docs/readme.md
 * // Compiled file: _site/docs/readme.html
 * Route::get('docs/readme')
 * ```
 */
final class RouteKey implements Stringable
{
    public function __construct(private readonly string $key)
    {
    }

    public function __toString()
    {
        return $this->key;
    }
}
