<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class StringField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::String;

    public static function parseInput(string $input): string
    {
        return $input;
    }

    public static function toYamlType(string $input): string
    {
        return $input;
    }
}
