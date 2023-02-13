<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use function array_filter;
use function array_merge;
use function blank;
use Illuminate\Support\Arr;

/**
 * Adds methods to a class to allow it to fluently interact with the front matter.
 */
trait InteractsWithFrontMatter
{
    /**
     * Get a value from the computed page data, or fallback to the page's front matter, then to the default value.
     *
     * @return \Hyde\Markdown\Models\FrontMatter|mixed
     */
    public function data(string $key = null, mixed $default = null): mixed
    {
        return Arr::get(array_filter(array_merge(
            $this->matter->toArray(),
            (array) $this,
        )), $key, $default);
    }

    /**
     * Get the front matter object, or a value from within.
     *
     * @return \Hyde\Markdown\Models\FrontMatter|mixed
     */
    public function matter(string $key = null, mixed $default = null): mixed
    {
        return $this->matter->get($key, $default);
    }

    /**
     * See if a value exists in the computed page data or the front matter.
     */
    public function has(string $key): bool
    {
        return ! blank($this->data($key));
    }
}
