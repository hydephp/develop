<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class UrlField extends PublicationField
{
    public const TYPE = PublicationFieldTypes::Url;

    protected static function parseInput(string $input): string
    {
        if (! filter_var($input, FILTER_VALIDATE_URL)) {
            throw self::parseError($input);
        }

        return $input;
    }
}
