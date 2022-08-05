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

    protected const SEARCH = '@php($';

    public function __construct(string $contents)
    {
        $this->contents = $contents;
    }

    public function get(): array
    {
        return $this->matter;
    }

    public function parse(): static
    {
        $this->matter = [];

        $lines = explode("\n", $this->contents);

        foreach ($lines as $line) {
            if (static::lineMatchesFrontMatter($line)) {
                $this->matter[] = static::parseLine($line);
            }
        }

        return $this;
    }

    protected static function lineMatchesFrontMatter(string $line): bool
    {
        return str_starts_with($line, static::SEARCH);
    }

    protected static function parseLine(string $line): array
    {
        return [static::extractKey($line) => static::extractValue($line)];
    }

    protected static function extractKey(string $line): string
    {
        return '';
    }

    protected static function extractValue(string $line): string
    {
        return '';
    }
}
