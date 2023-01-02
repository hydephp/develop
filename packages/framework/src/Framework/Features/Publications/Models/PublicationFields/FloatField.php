<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class FloatField extends PublicationField
{
    public const TYPE = PublicationFieldTypes::Float;

    public static function rules(): array
    {
        return ['numeric'];
    }
}
