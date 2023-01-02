<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\ValidatesPublicationField;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\ValidatesPublicationField
 */
class ValidatesPublicationsTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(ValidatesPublicationField::class, new ValidatesPublicationField(
            $this->createMock(PublicationType::class),
            $this->createMock(PublicationFieldDefinition::class)
        ));
    }
}
