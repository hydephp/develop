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
                $this->matter[static::extractKey($line)] = static::normalizeValue(static::extractValue($line));
            }
        }

        return $this;
    }

    protected static function lineMatchesFrontMatter(string $line): bool
    {
        return str_starts_with($line, static::SEARCH);
    }

    protected static function extractKey(string $line): string
    {
        // Remove search prefix
        $key = substr($line, strlen(static::SEARCH));

        // Remove everything after the first equals sign
        $key = substr($key, 0, strpos($key, '='));

        // Return trimmed line
        return trim($key);
    }

    protected static function extractValue(string $line): string
    {
        // Trim any trailing spaces and newlines
        $key = trim($line);

        // Remove everything before the first equals sign
        $key = substr($key, strpos($key, '=') + 1);

        // Remove closing parenthesis
        $key = substr($key, 0, strlen($key) - 1);

        // Remove any quotes so we can normalize the value
        $key = trim($key, ' "\'');

        // Return trimmed line
        return trim($key);
    }

    protected static function normalizeValue($value): mixed
    {
        // This will cast integers, floats, and booleans to their respective types
        // Still working on a way to handle arrays and objects
        return json_decode($value) ?? $value;
    }
}
