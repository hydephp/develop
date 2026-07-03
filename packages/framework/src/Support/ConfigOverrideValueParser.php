<?php

declare(strict_types=1);

namespace Hyde\Support;

use function is_numeric;
use function strtolower;

/** Parses a raw `--config=key=value` override's value string into a typed config value. */
class ConfigOverrideValueParser
{
    public static function parse(string $value): string|int|float|bool|null
    {
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => is_numeric($value) ? $value + 0 : $value,
        };
    }
}
