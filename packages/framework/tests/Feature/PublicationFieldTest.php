<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;
use InvalidArgumentException;
use ValueError;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationField
 */
class PublicationFieldTest extends TestCase
{
    public function test_can_instantiate_class()
    {
        $field = $this->makeField();
        $this->assertInstanceOf(PublicationField::class, $field);

        $this->assertSame(PublicationFieldTypes::String, $field->type);
        $this->assertSame('test', $field->name);
        $this->assertSame('1', $field->min);
        $this->assertSame('10', $field->max);
    }

    public function test_from_array_method()
    {
        $field = PublicationField::fromArray([
            'type' => 'string',
            'name' => 'test',
            'min'  => '1',
            'max'  => '10',
        ]);

        $this->assertInstanceOf(PublicationField::class, $field);

        $this->assertSame(PublicationFieldTypes::String, $field->type);
        $this->assertSame('test', $field->name);
        $this->assertSame('1', $field->min);
        $this->assertSame('10', $field->max);
    }

    public function test_can_get_field_as_array()
    {
        $this->assertSame([
            'type' => 'string',
            'name' => 'test',
            'min'  => '1',
            'max'  => '10',
            'tagGroup' => null,
        ], $this->makeField()->toArray());
    }

    public function test_can_encode_field_as_json()
    {
        $this->assertSame('{"type":"string","name":"test","min":"1","max":"10","tagGroup":null}', json_encode($this->makeField()));
    }

    public function test_can_construct_type_using_enum_case()
    {
        $field1 = new PublicationField(PublicationFieldTypes::String, 'test', 1, 10);
        $this->assertSame(PublicationFieldTypes::String, $field1->type);

        $field2 = new PublicationField('string', 'test', 1, 10);
        $this->assertSame(PublicationFieldTypes::String, $field2->type);

        $this->assertEquals($field1, $field2);
    }

    public function test_default_range_values()
    {
        $field = new PublicationField('string', 'test');
        $this->assertSame('0', $field->min);
        $this->assertSame('0', $field->max);
    }

    public function test_null_range_values_are_cast_to_empty_string()
    {
        $field = new PublicationField('string', 'test', null, null);
        $this->assertSame('', $field->min);
        $this->assertSame('', $field->max);
    }

    public function test_max_value_cannot_be_less_than_min_value()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'max' value cannot be less than the 'min' value.");

        new PublicationField('string', 'test', '10', '1');
    }

    public function test_only_min_value_can_be_set()
    {
        new PublicationField('string', 'test', '1');
        $this->assertTrue(true);
    }

    public function test_only_max_value_can_be_set()
    {
        new PublicationField('string', 'test', null, '10');
        $this->assertTrue(true);
    }

    public function test_integers_can_be_added_as_strings()
    {
        $field = new PublicationField('string', 'test', '1', '10');
        $this->assertSame('1', $field->min);
        $this->assertSame('10', $field->max);
    }

    public function test_type_must_be_valid()
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"invalid" is not a valid backing value for enum "'.PublicationFieldTypes::class.'"');

        new PublicationField('invalid', 'test', '1', '10');
    }

    public function test_type_input_is_case_insensitive()
    {
        $field = new PublicationField('STRING', 'test', '1', '10');
        $this->assertSame(PublicationFieldTypes::String, $field->type);
    }

    public function test_name_gets_stored_as_kebab_case()
    {
        $field = new PublicationField('string', 'Test Field', '1', '10');
        $this->assertSame('test-field', $field->name);
    }

    public function test_validate_input_against_rules()
    {
        $this->markTestIncomplete('TODO: Implement this method.');
    }

    protected function makeField(): PublicationField
    {
        return new PublicationField('string', 'test', 1, '10');
    }
}
