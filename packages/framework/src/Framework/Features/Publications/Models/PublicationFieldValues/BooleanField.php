<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use InvalidArgumentException;

final class BooleanField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Boolean;

    protected static function parseInput(string $input): bool
    {
        return match ($input) {
            'true', '1' => true,
            'false', '0' => false,
            default => throw new InvalidArgumentException("BooleanField: Unable to parse invalid boolean value '$input'")
        };
    }
}
