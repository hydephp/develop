<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class TextField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Text;

    protected static function parseInput(string $input): string
    {
        return $input;
    }
}
