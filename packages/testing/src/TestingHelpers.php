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

    protected function assertEqualsIgnoringLineEndingType(string $expected, string $actual): void
    {
        $this->assertEquals(
            $this->normalizeNewlines($expected),
            $this->normalizeNewlines($actual),
        );
    }
}
