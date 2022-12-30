<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

use InvalidArgumentException;

use function in_array;

final class BooleanField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Boolean;

    protected static function parseInput(string $input): bool
    {
        $acceptable = ['true', 'false', '0', '1'];

        if (! in_array($input, $acceptable, true)) {
            throw new InvalidArgumentException("BooleanField: Unable to parse invalid boolean value '$input'");
        }

        return (bool) $input;
    }
}
