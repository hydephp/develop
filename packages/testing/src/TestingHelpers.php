<?php

declare(strict_types=1);

namespace Hyde\Testing;

trait TestingHelpers
{
    final protected static function stripNewlines(string $string): string
    {
        return str_replace(["\r", "\n"], '', $string);
    }

    final protected static function normalizeNewlines(string $string): string
    {
        return str_replace(["\r\n"], "\n", $string);
    }

    /** @deprecated I think in general we should try to normalize all data input to use Unix endings so we don't need this  */
    final protected static function assertEqualsIgnoringLineEndingType(string $expected, string $actual): void
    {
        self::assertEquals(
            self::normalizeNewlines($expected),
            self::normalizeNewlines($actual),
        );
    }
}
