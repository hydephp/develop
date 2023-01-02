<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use JetBrains\PhpStorm\Deprecated;
use function collect;
use function Hyde\evaluate_arrayable;
use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use function validator;

/**
 * @see \Hyde\Framework\Testing\Feature\ValidatesPublicationsTest
 */
class ValidatesPublicationField
{
    protected PublicationType $publicationType;
    protected PublicationFieldDefinition $fieldDefinition;

    public function __construct(PublicationType $publicationType, PublicationFieldDefinition $fieldDefinition)
    {
        $this->publicationType = $publicationType;
        $this->fieldDefinition = $fieldDefinition;
    }

    /** @deprecated This will only be handled when validating using instance methods */
    public static function makeDynamicValidationRulesForPublicationFieldEntry(Models\PublicationFieldDefinition $fieldDefinition, ?PublicationType $publicationType): array
    {
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

    public static function getCustomRulesFromPublicationTypeSchema(PublicationFieldDefinition $fieldDefinition): array
    {
        return $fieldDefinition->rules;
    }

    public static function getDefaultRulesForFieldType(PublicationFieldTypes $type): array
    {
        return $type->rules();
    }

    public static function getValidationRulesForPublicationFieldDefinition(#[Deprecated]?PublicationType $publicationType, PublicationFieldDefinition $fieldDefinition): array
    {
        return array_merge(
            self::getDefaultRulesForFieldType($fieldDefinition->type),
            self::makeDynamicValidationRulesForPublicationFieldEntry($fieldDefinition, $publicationType),
            self::getCustomRulesFromPublicationTypeSchema($fieldDefinition)
        );
    }

    public function getValidationRules(): Collection
    {
        return collect(self::getValidationRulesForPublicationFieldDefinition($this->publicationType, $this->fieldDefinition));
    }

    public function validate(mixed $input = null, #[Deprecated]Arrayable|array|null $fieldRules = null): array
    {
        $rules = evaluate_arrayable($fieldRules ?? $this->getValidationRules());

        return validator(
            [$this->fieldDefinition->name => $input],
            [$this->fieldDefinition->name => $rules]
        )->validate();
    }
}
