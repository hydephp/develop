<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Testing\TestCase;
use InvalidArgumentException;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldType
 */
class PublicationFieldTypeTest extends TestCase
{
    public function test_can_instantiate_class()
    {
        $field = $this->makeField();
        $this->assertInstanceOf(PublicationFieldType::class, $field);

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
        $field = new PublicationFieldType('string', 'test', null, null);
        $this->assertNull($field->min);
        $this->assertNull($field->max);
    }

    public function test_max_value_cannot_be_less_than_min_value()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'max' value cannot be less than the 'min' value.");

        new PublicationFieldType('string', 'test', 10, 1);
    }

    public function test_integers_can_be_added_as_strings()
    {
        $field = new PublicationFieldType('string', 'test', 1, '10');
        $this->assertSame(1, $field->min);
        $this->assertSame(10, $field->max);
    }

    public function test_type_must_be_valid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The type 'invalid' is not a valid type. Valid types are: string, boolean, integer, float, datetime, url, array, text, image.");

        new PublicationFieldType('invalid', 'test', 1, 10);
    }

    public function test_type_input_is_case_insensitive()
    {
        $field = new PublicationFieldType('STRING', 'test', 1, 10);
        $this->assertSame('string', $field->type);
    }

    public function test_name_gets_stored_as_kebab_case()
    {
        $field = new PublicationFieldType('string', 'Test Field', 1, 10);
        $this->assertSame('test-field', $field->name);
    }

    public function test_validate_input_against_rules()
    {
        $this->markTestIncomplete('TODO: Implement this method.');
    }

    public function test_types_constant()
    {
        $this->assertSame(['string', 'boolean', 'integer', 'float', 'datetime', 'url', 'array', 'text', 'image'], PublicationFieldType::TYPES);
    }

    protected function makeField(): PublicationFieldType
    {
        return new PublicationFieldType('string', 'test', 1, 10);
    }
}
