<?php

namespace Hyde\Framework\Actions;

/**
 * Parse the front matter in a Blade file.
 *
 * Accepts a string to make it easier to mock when testing.
 */
class BladeMatterParser
{
    protected string $contents;
    protected array $matter;

    public function __construct(string $contents)
    {
        $this->contents = $contents;
    }

    public function parse(): static
    {
        $this->matter = [];

        return $this;
    }

    public function get(): array
    {
        return $this->matter;
    }
}
