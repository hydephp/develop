<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

use function is_array;

final class ArrayField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Array;
    public const PARSE_FROM_CSV = 4;
    public const PARSE_FROM_NEWLINES = 8;

    public function __construct(string|array $value, int $options = 0)
    {
        $this->value = self::parseInput($value, $options);
    }

    protected static function parseInput(string|array $input, int $options = 0): array
    {
        if (is_array($input)) {
            return $input;
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
