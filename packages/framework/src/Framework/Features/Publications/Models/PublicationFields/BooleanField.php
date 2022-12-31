<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class BooleanField extends PublicationField
{
    public const TYPE = PublicationFieldTypes::Boolean;

    protected static function parseInput(string $input): bool
    {
        return match ($input) {
            'true', '1' => true,
            'false', '0' => false,
            default => throw self::parseError($input)
        };
    }
}
