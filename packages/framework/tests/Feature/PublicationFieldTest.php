<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Testing\TestCase;
use InvalidArgumentException;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationField
 */
class PublicationFieldTest extends TestCase
{
    public function test_can_instantiate_class()
    {
        $field = $this->makeField();
        $this->assertInstanceOf(PublicationField::class, $field);

        $this->assertSame('string', $field->type);
        $this->assertSame('test', $field->name);
        $this->assertSame(1, $field->min);
        $this->assertSame(10, $field->max);
    }

    public function test_can_get_field_as_array()
    {
        $this->assertSame([
            'type' => 'string',
            'name' => 'test',
            'min'  => 1,
            'max'  => 10,
        ], $this->makeField()->toArray());
    }

    public function test_can_encode_field_as_json()
    {
        $this->assertSame('{"type":"string","name":"test","min":1,"max":10}', json_encode($this->makeField()));
    }

    public function test_range_values_can_be_null()
    {
        $field = new PublicationField('string', 'test', null, null);
        $this->assertNull($field->min);
        $this->assertNull($field->max);
    }

    public function test_max_value_cannot_be_less_than_min_value()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'max' value cannot be less than the 'min' value.");

        new PublicationField('string', 'test', 10, 1);
    }

    public function test_validate_input_against_rules()
    {
        $this->markTestIncomplete('TODO: Implement this method.');
    }

    protected function makeField(): PublicationField
    {
        return new PublicationField('string', 'test', 1, 10);
    }
}
