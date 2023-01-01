<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class ImageField extends PublicationField
{
    public const TYPE = PublicationFieldTypes::Image;

    protected static function parseInput(string $input): string
    {
        // TODO Validate file exists?
        return $input;
    }

    public static function rules(): array
    {
        return [];
    }
}
