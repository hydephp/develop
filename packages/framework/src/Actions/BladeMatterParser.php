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
        $lines = explode("\n", $this->contents);

        foreach ($lines as $line) {
            if (static::lineMatchesFrontMatter($line)) {
                $this->matter[] = static::parseLine($line);
            }
        }

        $this->matter = [];

        return $this;
    }

    public function get(): array
    {
        return $this->matter;
    }

    protected static function lineMatchesFrontMatter(string $line): bool
    {
        return false;
    }

    protected static function parseLine(string $line): array
    {
        return [];
    }
}
