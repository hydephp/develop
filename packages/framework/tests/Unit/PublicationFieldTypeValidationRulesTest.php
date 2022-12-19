<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldType
 */
class PublicationFieldTypeValidationRulesTest extends TestCase
{
    protected function makePublicationType(array $fields = []): PublicationType
    {
        return new PublicationType(
            'test',
            '__createdAt',
            fields: $fields,
            directory: 'test-publication',
        );
    }
}
