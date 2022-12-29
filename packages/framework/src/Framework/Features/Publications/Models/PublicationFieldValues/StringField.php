<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

class StringField extends PublicationFieldValue
{
    public static function parseInput(string $input): string
    {
        return $input;
    }

    public static function toYamlType(string $input): string
    {
        return $input;
    }
}
