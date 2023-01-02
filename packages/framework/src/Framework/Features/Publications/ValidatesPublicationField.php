<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use function array_merge;
use function collect;
use function Hyde\evaluate_arrayable;
use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationType;
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

    public function getValidationRules(): Collection
    {
        return collect(array_merge(
            self::getValidationRulesForPublicationFieldDefinition($this->fieldDefinition),
            $this->makeDynamicValidationRulesForPublicationFieldEntry()
        ));
    }

    public function validate(mixed $input = null): array
    {
        $rules = evaluate_arrayable($fieldRules ?? $this->getValidationRules());

        return validator(
            [$this->fieldDefinition->name => $input],
            [$this->fieldDefinition->name => $rules]
        )->validate();
    }

    protected function makeDynamicValidationRulesForPublicationFieldEntry(): array
    {
        if ($this->fieldDefinition->type == PublicationFieldTypes::Image) {
            if ($this->publicationType !== null) {
                $mediaFiles = PublicationService::getMediaForPubType($this->publicationType);
                $valueList = $mediaFiles->implode(',');
            } else {
                $valueList = '';
            }

            return ["in:$valueList"];
        }

        if ($this->fieldDefinition->type == PublicationFieldTypes::Tag) {
            if ($this->publicationType !== null) {
                $tagValues = PublicationService::getValuesForTagName($this->publicationType->getIdentifier()) ?? collect([]);
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

    public static function getValidationRulesForPublicationFieldDefinition(PublicationFieldDefinition $fieldDefinition): array
    {
        return array_merge(
            self::getDefaultRulesForFieldType($fieldDefinition->type),
            self::getCustomRulesFromPublicationTypeSchema($fieldDefinition)
        );
    }

    protected static function getDefaultRulesForFieldType(PublicationFieldTypes $type): array
    {
        return $type->rules();
    }
}
