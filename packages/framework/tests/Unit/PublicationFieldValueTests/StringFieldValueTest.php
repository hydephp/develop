<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\PublicationFieldValueTests;

use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\StringField;

require_once __DIR__.'/BaseFieldValueTest.php';

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\StringField
 */
class StringFieldValueTest extends BaseFieldValueTest
{
    /** @var class-string|\Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue */
    protected static string|PublicationFieldValue $fieldClass = StringField::class;
}
