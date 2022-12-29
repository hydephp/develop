<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

/**
 * @see \Hyde\Framework\Features\Publications\PublicationFieldTypes
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldValueObjectsTest
 */
abstract class PublicationFieldValue
{
    protected PublicationFieldTypes $type;
    protected mixed $value;

    final public function __construct(PublicationFieldTypes $type, string $value)
    {
        $this->type = $type;
        $this->value = static::parseInput($value);
    }

    final public function getValue(): mixed
    {
        return static::toYamlType($this->value);
    }

    /**
     * Parse an input string from the command line into a value with the appropriate type for this field.
     *
     * @param  string  $input
     * @return mixed
     */
    abstract public static function parseInput(string $input): mixed;

    /**
     * Return the value with the appropriate type for this field's YAML representation.
     *
     * @param  string  $input
     * @return mixed
     */
    abstract public static function toYamlType(string $input): mixed;
}
