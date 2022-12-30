<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use InvalidArgumentException;

final class IntegerField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Integer;

    protected static function parseInput(string $input): int
    {
        if (! is_numeric($input)) {
            throw new InvalidArgumentException("IntegerField: Unable to parse invalid integer value '$input'");
        }

        return (int) $input;
    }
}
