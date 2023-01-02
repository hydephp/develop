<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class UrlField extends PublicationField
{
    public const TYPE = PublicationFieldTypes::Url;

    public static function rules(): array
    {
        return ['url'];
    }
}
