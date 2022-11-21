<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationField
 */
class PublicationFieldTest extends TestCase
{
    public function test_can_instantiate_class()
    {
        $field = new PublicationField('test', '1', '10', 'string');
        $this->assertInstanceOf(PublicationField::class, $field);

        $this->assertSame('test', $field->name);
        $this->assertSame('1', $field->min);
        $this->assertSame('10', $field->max);
        $this->assertSame('string', $field->type);
    }
}
