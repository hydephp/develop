<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

/**
 * @see \Hyde\Framework\Features\Publications\PublicationFieldTypes
 */
abstract class PublicationFieldValue
{
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
