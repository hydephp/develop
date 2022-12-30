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
        $true = ['true', '1'];
        $false = ['false', '0'];

        if (in_array($input, $true)) {
            return true;
        }

        if (in_array($input, $false)) {
            return false;
        }

        throw new InvalidArgumentException("BooleanField: Unable to parse invalid boolean value '$input'");
    }
}
