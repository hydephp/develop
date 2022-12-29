<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use DateTime;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class DatetimeField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Datetime;

    protected static function parseInput(string $input): DateTime
    {
        return new DateTime($input);
    }

    protected static function toYamlType(mixed $input): DateTime
    {
        return $input;
    }
}
