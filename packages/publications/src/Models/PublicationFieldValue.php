<?php

/** @noinspection PhpDuplicateMatchArmBodyInspection */

declare(strict_types=1);

namespace Hyde\Publications\Models;

use DateTime;
use Hyde\Publications\Concerns\PublicationFieldTypes;
use Hyde\Publications\Concerns\ParsesPublicationFieldInputs;

use function is_array;

/**
 * Represents a single value for a field in a publication's front matter,
 * following rules defined in the "fields" array of the publication type's schema.
 *
 * @see \Hyde\Publications\Models\PublicationFieldDefinition
 * @see \Hyde\Publications\Concerns\PublicationFieldTypes
 * @see \Hyde\Publications\Testing\Feature\PublicationFieldValueTest
 */
final class PublicationFieldValue
{
    use ParsesPublicationFieldInputs;

    public readonly PublicationFieldTypes $type;
    protected string|array|bool|float|int|DateTime $value;

    public function __construct(PublicationFieldTypes $type, string|array $value)
    {
        $this->type = $type;

        if (is_array($value)) {
            // This means the value is already parsed and validated
            $this->value = $value;
        } else {
            $this->value = self::parseFieldValue($type, $value);
        }
    }

    public function getType(): PublicationFieldTypes
    {
        return $this->type;
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
            PublicationFieldTypes::Media => self::parseMediaValue($value),
            PublicationFieldTypes::Array => self::parseArrayValue($value),
            PublicationFieldTypes::Text => self::parseTextValue($value),
            PublicationFieldTypes::Url => self::parseUrlValue($value),
            PublicationFieldTypes::Tag => self::parseTagValue($value),
        };
    }
}
