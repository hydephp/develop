<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class BooleanField extends PublicationField
{
    public const TYPE = PublicationFieldTypes::Boolean;
}
