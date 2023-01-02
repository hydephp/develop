<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class TextField extends PublicationField
{
    public const TYPE = PublicationFieldTypes::Text;
}
