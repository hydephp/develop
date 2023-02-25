<?php

declare(strict_types=1);

namespace Hyde\Support;

class BuildWarnings
{
    protected static array $warnings = [];

    public static function add(string $warning): void
    {
        self::$warnings[] = $warning;
    }

    public static function get(): array
    {
        return self::$warnings;
    }

    public static function hasWarnings(): bool
    {
        return count(self::$warnings) > 0;
    }

    public static function clear(): void
    {
        self::$warnings = [];
    }
}
