<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

/**
 * Represents a single value for a field in a publication,
 * as defined in the "fields" array of a publication type schema.
 *
 * @deprecated
 * @see \Hyde\Framework\Features\Publications\PublicationFieldTypes
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldServiceTest
 */
abstract class PublicationField
{
    /** @var \Hyde\Framework\Features\Publications\PublicationFieldTypes */
    public const TYPE = null;

    protected mixed $value;

    public function __construct(string $value = null)
    {
        if ($value !== null) {
            $this->value = PublicationFieldValue::parseFieldValue(static::TYPE, $value);
        }
    }

    final public function getValue(): mixed
    {
        return $this->value;
    }
}
