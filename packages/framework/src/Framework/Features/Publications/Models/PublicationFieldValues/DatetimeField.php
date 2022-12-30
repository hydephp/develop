<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use DateTime;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\Contracts\Canonicable;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class DatetimeField extends PublicationFieldValue implements Canonicable
{
    use CanonicableTrait;

    public const TYPE = PublicationFieldTypes::Datetime;

    protected static function parseInput(string $input): DateTime
    {
        return new DateTime($input);
    }
}
