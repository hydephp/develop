<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use function class_basename;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use InvalidArgumentException;
use RuntimeException;
use function str;

/**
 * @see \Hyde\Framework\Features\Publications\PublicationFieldTypes
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldValueObjectsTest
 */
abstract class PublicationField
{
    /** @var \Hyde\Framework\Features\Publications\PublicationFieldTypes */
    public const TYPE = null;

    protected mixed $value;

    public function __construct(string $value = null)
    {
        if ($value !== null) {
            $this->value = static::parseInput($value);
        }
    }

    final public function getValue(): mixed
    {
        return $this->value;
    }

    final public static function getType(): PublicationFieldTypes
    {
        return static::TYPE ?? throw new RuntimeException('PublicationField::TYPE must be set in child class.');
    }

    /**
     * Parse an input string from the command line into a value with the appropriate type for this field.
     *
     * @param  string  $input
     * @return mixed
     */
    abstract protected static function parseInput(string $input): mixed;

    protected static function parseError(string $input): InvalidArgumentException
    {
        $className = class_basename(static::class);
        $typeName = str($className)->replace('Field', '')->snake()->__toString();

        return new InvalidArgumentException("$className: Unable to parse invalid $typeName value '$input'");
    }

    public function getRules(?PublicationFieldDefinition $fieldDefinition = null): array
    {
        return $fieldDefinition?->getValidationRules() ?? [];
    }
}
