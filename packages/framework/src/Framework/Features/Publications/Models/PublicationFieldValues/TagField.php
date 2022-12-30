<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class TagField extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::Tag;

    protected static function parseInput(string $input, ?array $useArrayLiteral = null): array
    {
        if ($useArrayLiteral !== null) {
            return $useArrayLiteral;
        }

        return (array) $input;
    }
}
