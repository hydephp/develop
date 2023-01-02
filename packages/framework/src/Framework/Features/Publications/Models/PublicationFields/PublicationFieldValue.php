<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\PublicationFieldService;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;

use function is_array;

/**
 * Represents a single value for a field in a publication,
 * as defined in the "fields" array of a publication type schema.
 *
 * @see \Hyde\Framework\Features\Publications\PublicationFieldTypes
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldServiceTest
 */
class PublicationFieldValue
{
    public readonly PublicationFieldTypes $type;
    protected mixed $value;

    public function __construct(PublicationFieldTypes $type, string|array $value)
    {
        if (is_array($value)) {
            // This means the value is already parsed and validated
            $this->value = $value;
        } else {
            $this->value = PublicationFieldService::parseFieldValue($type, $value);
        }
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
