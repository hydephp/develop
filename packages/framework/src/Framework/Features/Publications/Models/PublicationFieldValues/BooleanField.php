<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class BooleanField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Boolean;

    protected static function parseInput(string $input): bool
    {
        return (bool) $input;
    }

    protected static function toYamlType(mixed $input): bool
    {
        return (bool) $input;
    }
}
