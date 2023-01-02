<?php

/** @noinspection PhpDuplicateMatchArmBodyInspection */

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use function array_merge;
use function collect;
use DateTime;
use function filter_var;
use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\Validation\BooleanRule;
use InvalidArgumentException;
use function is_numeric;

/**
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\StringField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\DatetimeField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\BooleanField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\IntegerField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\FloatField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\ImageField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\ArrayField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\TextField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\UrlField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\TagField
 */
class PublicationFieldService
{
    public static function normalizeFieldValue(PublicationFieldTypes $fieldType, mixed $value)
    {
        return match ($fieldType) {
            PublicationFieldTypes::String => self::normalizeStringValue($value),
            PublicationFieldTypes::Datetime => self::normalizeDatetimeValue($value),
            PublicationFieldTypes::Boolean => self::normalizeBooleanValue($value),
            PublicationFieldTypes::Integer => self::normalizeIntegerValue($value),
            PublicationFieldTypes::Float => self::normalizeFloatValue($value),
            PublicationFieldTypes::Image => self::normalizeImageValue($value),
            PublicationFieldTypes::Array => self::normalizeArrayValue($value),
            PublicationFieldTypes::Text => self::normalizeTextValue($value),
            PublicationFieldTypes::Url => self::normalizeUrlValue($value),
            PublicationFieldTypes::Tag => self::normalizeTagValue($value),
        };
    }

    public static function getDefaultValidationRulesForFieldType(PublicationFieldTypes $fieldType): array
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

    public static function getValidationRulesForPublicationFieldEntry(PublicationType $publicationType, string $fieldName): array
    {
        return self::getValidationRulesForPublicationFieldDefinition($publicationType,
            $publicationType->getFieldDefinition($fieldName)
        );
    }

    public static function getValidationRulesForPublicationFieldDefinition(?PublicationType $publicationType, PublicationFieldDefinition $fieldDefinition): array
    {
        return array_merge(
            self::getDefaultValidationRulesForFieldType($fieldDefinition->type),
            self::makeDynamicValidationRulesForPublicationFieldEntry($fieldDefinition, $publicationType),
            $fieldDefinition->rules
        );
    }

    protected static function makeDynamicValidationRulesForPublicationFieldEntry(
        Models\PublicationFieldDefinition $fieldDefinition, ?PublicationType $publicationType
    ): array {
        if ($fieldDefinition->type == PublicationFieldTypes::Image) {
            if ($publicationType !== null) {
                $mediaFiles = PublicationService::getMediaForPubType($publicationType);
                $valueList = $mediaFiles->implode(',');
            } else {
                $valueList = '';
            }

            return ["in:$valueList"];
        }

        if ($fieldDefinition->type == PublicationFieldTypes::Tag) {
            if ($publicationType !== null) {
                $tagValues = PublicationService::getValuesForTagName($publicationType->getIdentifier()) ?? collect([]);
                $valueList = $tagValues->implode(',');
            } else {
                $valueList = '';
            }

            return ["in:$valueList"];
        }

        return [];
    }

    protected static function parseError(string $typeName, string $input): InvalidArgumentException
    {
        $className = ucfirst($typeName).'Field';

        return new InvalidArgumentException("$className: Unable to parse invalid $typeName value '$input'");
    }

    public static function normalizeStringValue(mixed $value): mixed
    {
        return $value;
    }

    public static function normalizeDatetimeValue(mixed $value): DateTime
    {
        return new DateTime($value);
    }

    public static function normalizeBooleanValue(mixed $value): bool
    {
        return match ($value) {
            'true', '1' => true,
            'false', '0' => false,
            default => throw self::parseError('boolean', $value)
        };
    }

    public static function normalizeIntegerValue(mixed $value): int
    {
        if (! is_numeric($value)) {
            throw self::parseError('integer', $value);
        }

        return (int) $value;
    }

    public static function normalizeFloatValue(mixed $value): float
    {
        if (! is_numeric($value)) {
            throw self::parseError('float', $value);
        }

        return (float) $value;
    }

    public static function normalizeImageValue(mixed $value): mixed
    {
        // TODO Validate file exists?
        return $value;
    }

    public static function normalizeArrayValue(mixed $value): array
    {
        return (array) $value;
    }

    public static function normalizeTextValue(mixed $value): mixed
    {
        // In order to properly store multi-line text fields as block literals,
        // we need to make sure the string ends with a newline character.

        if (substr_count($value, "\n") > 0) {
            return trim($value, "\r\n")."\n";
        }

        return $value;
    }

    public static function normalizeUrlValue(mixed $value): mixed
    {
        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            throw self::parseError('url', $value);
        }

        return $value;
    }

    public static function normalizeTagValue(mixed $value): array
    {
        return (array) $value;
    }
}
