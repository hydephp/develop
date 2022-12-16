<?php

declare(strict_types=1);

namespace Hyde\Testing;

trait TestingHelpers
{
    final protected static function normalizeNewlines(string $string): string
    {
        return str_replace(["\r\n"], "\n", $string);
    }
}
