<?php

namespace Hyde\Framework\Concerns\Internal;

/**
 * @internal This trait is not meant to be used outside of Hyde.
 *
 * Handles the front matter accessors and logic for Hyde pages.
 */
trait HandlesPageMatter
{
    /**
     * Get a value from the computed page data, or fallback to the page's front matter, then to the default value.
     *
     * @return \Hyde\Framework\Models\FrontMatter|mixed
     */
    public function get(string $key = null, mixed $default = null): mixed
    {
        if ($key !== null && property_exists($this, $key) && isset($this->$key)) {
            return $this->$key;
        }

        return $this->matter($key, $default);
    }

    /**
     * Get the front matter object, or a value from within.
     *
     * @return \Hyde\Framework\Models\FrontMatter|mixed
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
        return ! blank($this->get($key));
    }
}