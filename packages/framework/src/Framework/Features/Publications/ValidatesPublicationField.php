<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use function array_merge;
use function collect;
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
        $rules = $this->getValidationRules();

        return validator(
            [$this->fieldDefinition->name => $input],
            [$this->fieldDefinition->name => $rules]
        )->validate();
    }

    protected function makeDynamicValidationRulesForPublicationFieldEntry(): array
    {
        if ($this->fieldDefinition->type == PublicationFieldTypes::Image) {
            $mediaFiles = PublicationService::getMediaForPubType($this->publicationType);
            $valueList = $mediaFiles->implode(',');

            return ["in:$valueList"];
        }

        if ($this->fieldDefinition->type == PublicationFieldTypes::Tag) {
            $tagValues = PublicationService::getValuesForTagName($this->publicationType->getIdentifier()) ?? collect([]);
            $valueList = $tagValues->implode(',');

            return ["in:$valueList"];
        }

        return [];
    }

    public static function getValidationRulesForPublicationFieldDefinition(PublicationFieldDefinition $fieldDefinition): array
    {
        return array_merge(
            $fieldDefinition->type->rules(),
            $fieldDefinition->rules
        );
    }
}
