<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class IntegerField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Integer;

    protected static function parseInput(string $input): int
    {
        return (int) $input;
    }

    protected static function toYamlType(mixed $input): int
    {
        return (int) $input;
    }
}
