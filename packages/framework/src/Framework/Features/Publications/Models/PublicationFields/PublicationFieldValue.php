<?php

/** @noinspection PhpDuplicateMatchArmBodyInspection */

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use DateTime;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\Validation\BooleanRule;
use InvalidArgumentException;
use function is_array;

/**
 * Represents a single value for a field in a publication,
 * as defined in the "fields" array of a publication type schema.
 *
 * @see \Hyde\Framework\Features\Publications\PublicationFieldTypes
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldServiceTest
 */
final class PublicationFieldValue
{
    public readonly PublicationFieldTypes $type;
    protected string|array|bool|float|int|DateTime $value;

    public function __construct(PublicationFieldTypes $type, string|array $value)
    {
        if (is_array($value)) {
            // This means the value is already parsed and validated
            $this->value = $value;
        } else {
            $this->value = self::parseFieldValue($type, $value);
        }
    }

    public function getValue(): string|array|bool|float|int|DateTime
    {
        return $this->value;
    }

    /** Parse an input string from the command line into a value with the appropriate type for the field. */
    public static function parseFieldValue(PublicationFieldTypes $fieldType, string|array $value): string|array|bool|float|int|DateTime
    {
        return match ($fieldType) {
            PublicationFieldTypes::String => self::parseStringValue($value),
            PublicationFieldTypes::Datetime => self::parseDatetimeValue($value),
            PublicationFieldTypes::Boolean => self::parseBooleanValue($value),
            PublicationFieldTypes::Integer => self::parseIntegerValue($value),
            PublicationFieldTypes::Float => self::parseFloatValue($value),
            PublicationFieldTypes::Image => self::parseImageValue($value),
            PublicationFieldTypes::Array => self::parseArrayValue($value),
            PublicationFieldTypes::Text => self::parseTextValue($value),
            PublicationFieldTypes::Url => self::parseUrlValue($value),
            PublicationFieldTypes::Tag => self::parseTagValue($value),
        };
    }

    /** Get the default validation rules for a field type. */
    public static function getDefaultFieldRules(PublicationFieldTypes $fieldType): array
    {
        return match ($fieldType) {
            PublicationFieldTypes::String => ['string'],
            PublicationFieldTypes::Datetime => ['date'],
            PublicationFieldTypes::Boolean => [new BooleanRule],
            PublicationFieldTypes::Integer => ['integer', 'numeric'],
            PublicationFieldTypes::Float => ['numeric'],
            PublicationFieldTypes::Image => [],
            PublicationFieldTypes::Array => ['array'],
            PublicationFieldTypes::Text => ['string'],
            PublicationFieldTypes::Url => ['url'],
            PublicationFieldTypes::Tag => [],
        };
    }

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

    protected static function parseImageValue(string $value): string
    {
        // TODO Validate file exists as the dynamic validation rules does the same
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
        return new InvalidArgumentException(sprintf("%s: Unable to parse invalid %s value '%s'",
            (ucfirst($typeName).'Field'), $typeName, $input
        ));
    }
}
