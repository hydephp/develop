<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\Validation\BooleanRule;

final class BooleanField extends PublicationField
{
    public const TYPE = PublicationFieldTypes::Boolean;

    public static function rules(): array
    {
        return [new BooleanRule];
    }
}
