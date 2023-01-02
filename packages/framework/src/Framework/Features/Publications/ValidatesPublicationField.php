<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationType;

/**
 * @see \Hyde\Framework\Testing\Feature\ValidatesPublicationsTest
 */
class ValidatesPublicationField
{
    protected PublicationType $publicationType;
    protected PublicationFieldDefinition $fieldDefinition;

    public function __construct(PublicationType $publicationType, PublicationFieldDefinition $fieldDefinition)
    {
        $this->publicationType = $publicationType;
        $this->fieldDefinition = $fieldDefinition;
    }
}
