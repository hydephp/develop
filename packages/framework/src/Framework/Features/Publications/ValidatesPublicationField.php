<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Illuminate\Contracts\Support\Arrayable;

use Illuminate\Support\Collection;

use function collect;
use function Hyde\evaluate_arrayable;
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

    /**
     * @param  \Hyde\Framework\Features\Publications\Models\PublicationType|null  $publicationType  Required only when using the 'image' type.
     */
    public function getValidationRules(?PublicationType $publicationType = null): Collection
    {
        return collect(PublicationFieldService::getValidationRulesForPublicationFieldDefinition($publicationType, $this));
    }

    public function validate(mixed $input = null, Arrayable|array|null $fieldRules = null, ?PublicationType $publicationType = null): array
    {
        $rules = evaluate_arrayable($fieldRules ?? $this->getValidationRules($publicationType));

        return validator([$this->name => $input], [$this->name => $rules])->validate();
    }
}
