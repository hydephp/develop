<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use function array_merge;
use function collect;
use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Illuminate\Contracts\Validation\Validator;
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

    public function getValidationRules(): array
    {
        return array_merge(
            $this->fieldDefinition->getRules(),
            $this->makeDynamicRules()
        );
    }

    public function validate(mixed $input = null): array
    {
        return $this->makeValidator($input, $this->getValidationRules())->validate();
    }

    protected function makeDynamicRules(): array
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

    protected function makeValidator(mixed $input, array $rules): Validator
    {
        return validator(
            [$this->fieldDefinition->name => $input],
            [$this->fieldDefinition->name => $rules]
        );
    }
}
