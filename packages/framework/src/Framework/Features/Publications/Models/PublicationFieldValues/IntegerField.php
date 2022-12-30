<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class IntegerField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Integer;

    protected static function parseInput(string $input): int
    {
        if (! is_numeric($input)) {
            throw self::parseError($input);
        }

        return (int) $input;
    }
}
