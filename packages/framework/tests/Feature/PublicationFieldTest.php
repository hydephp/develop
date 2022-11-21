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
        $field = new PublicationField('string', 'test', '1', '10');
        $this->assertInstanceOf(PublicationField::class, $field);

        $this->assertSame('test', $field->name);
        $this->assertSame('1', $field->min);
        $this->assertSame('10', $field->max);
        $this->assertSame('string', $field->type);
    }

    public function test_can_get_field_as_array()
    {
        $field = new PublicationField('string', 'test', '1', '10');
        $this->assertSame([
            'type' => 'string',
            'name' => 'test',
            'min'  => '1',
            'max'  => '10',
        ], $field->toArray());
    }

    public function test_can_encode_field_as_json()
    {
        $field = new PublicationField('string', 'test', '1', '10');
        $this->assertSame('{"type":"string","name":"test","min":"1","max":"10"}', json_encode($field));
    }
}
