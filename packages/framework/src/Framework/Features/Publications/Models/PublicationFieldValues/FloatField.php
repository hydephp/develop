<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class FloatField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Float;

    protected static function parseInput(string $input): float
    {
        if (! is_numeric($input)) {
            throw self::parseError($input);
        }

        return (float) $input;
    }
}
