<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class ArrayField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Array;
    public const PARSE_FROM_CSV = 4;
    public const PARSE_FROM_NEWLINES = 8;

    public function __construct(string $value, int $options = 0, ?array $useArrayLiteral = null)
    {
        $this->value = self::parseInput($value, $options, $useArrayLiteral);
    }

    protected static function parseInput(string $input, int $options = 0, ?array $useArrayLiteral = null): array
    {
        if ($useArrayLiteral !== null) {
            return $useArrayLiteral;
        }

        if ($options & self::PARSE_FROM_CSV) {
            return explode(', ', $input);
        }

        if ($options & self::PARSE_FROM_NEWLINES) {
            return explode("\n", $input);
        }

        return (array) $input;
    }
}
