<?php

declare(strict_types=1);

namespace Hyde\Publications\Concerns;

use DateTime;
use InvalidArgumentException;

use function filter_var;
use function is_numeric;
use function substr_count;
use function trim;
use function ucfirst;

/**
 * @internal Single-use trait for the PublicationFieldValue class.
 *
 * @see \Hyde\Publications\Models\PublicationFieldValue
 */
trait ParsesPublicationFieldInputs
{
    protected static function parseStringValue(string $value): string
    {
        return $value;
    }

    protected static function parseDatetimeValue(string $value): DateTime
    {
        return new DateTime($value);
    }

    protected static function parseBooleanValue(string $value): bool
    {
        return match ($value) {
            'true', '1' => true,
            'false', '0' => false,
            default => throw self::parseError('boolean', $value)
        };
    }

    protected static function parseIntegerValue(string $value): int
    {
        if (! is_numeric($value)) {
            throw self::parseError('integer', $value);
        }

        return (int) $value;
    }

    protected static function parseFloatValue(string $value): float
    {
        if (! is_numeric($value)) {
            throw self::parseError('float', $value);
        }

        return (float) $value;
    }

    protected static function parseMediaValue(string $value): string
    {
        return $value;
    }

    protected static function parseArrayValue(string|array $value): array
    {
        return (array) $value;
    }

    protected static function parseTextValue(string $value): string
    {
        // In order to properly store multi-line text fields as block literals,
        // we need to make sure the string ends with a newline character.
        if (substr_count($value, "\n") > 0) {
            return trim($value, "\r\n")."\n";
        }

        return $value;
    }

    protected static function parseUrlValue(string $value): string
    {
        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            throw self::parseError('url', $value);
        }

        return $value;
    }

    protected static function parseTagValue(string|array $value): array
    {
        return (array) $value;
    }

    protected static function parseError(string $typeName, string $input): InvalidArgumentException
    {
        return new InvalidArgumentException(ucfirst("{$typeName}Field: Unable to parse invalid $typeName value '$input'"));
    }
}
