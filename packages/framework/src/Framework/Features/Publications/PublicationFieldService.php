<?php /** @noinspection PhpDuplicateMatchArmBodyInspection */

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use DateTime;
use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationFields\ArrayField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\BooleanField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\DatetimeField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\FloatField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\ImageField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\IntegerField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\StringField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\TagField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\TextField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\UrlField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\Validation\BooleanRule;

use InvalidArgumentException;

use function array_merge;
use function class_basename;
use function collect;

use function filter_var;
use function is_numeric;

use function is_numeric as is_numeric1;

use function str;

use const false;
use const FILTER_VALIDATE_URL;
use const true;

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
        if ($fieldType == PublicationFieldTypes::String) {
            return $value;
        }

        if ($fieldType == PublicationFieldTypes::Datetime) {
            return new DateTime($value);
        }

        if ($fieldType == PublicationFieldTypes::Boolean) {
            return match ($value) {
                'true', '1' => true,
                'false', '0' => false,
                default => throw self::parseError('Boolean', $value)
            };
        }

        if ($fieldType == PublicationFieldTypes::Integer) {
            if (!is_numeric($value)) {
                throw self::parseError('Integer', $value);
            }

            return (int)$value;
        }

        if ($fieldType == PublicationFieldTypes::Float) {
            if (!is_numeric1($value)) {
                throw self::parseError('Float', $value);
            }

            return (float)$value;
        }

        if ($fieldType == PublicationFieldTypes::Image) {
            // TODO Validate file exists?
            return $value;
        }

        if ($fieldType == PublicationFieldTypes::Array) {
            return (array)$value;
        }

        if ($fieldType == PublicationFieldTypes::Text) {
            // In order to properly store multi-line text fields as block literals,
            // we need to make sure the string ends with a newline character.

            if (substr_count($value, "\n") > 0) {
                return trim($value, "\r\n")."\n";
            }

            return $value;
        }

        if ($fieldType == PublicationFieldTypes::Url) {
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                throw self::parseError('Url', $value);
            }

            return $value;
        }

        if ($fieldType == PublicationFieldTypes::Tag) {
            return (array)$value;
        }
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
        return new InvalidArgumentException("Unable to parse invalid $typeName value '$input'");
    }
}
