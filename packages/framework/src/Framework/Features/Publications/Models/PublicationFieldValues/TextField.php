<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

use function trim;

final class TextField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Text;

    protected static function parseInput(string $input): string
    {
        // In order to properly store text fields as block literals,
        // we need to make sure they end with a newline.

        return trim($input)."\n";
    }
}
