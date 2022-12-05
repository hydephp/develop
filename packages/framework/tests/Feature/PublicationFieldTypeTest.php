<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Concerns\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Testing\TestCase;
use InvalidArgumentException;
use ValueError;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldType
 */
class PublicationFieldTypeTest extends TestCase
{
    public function test_can_instantiate_class()
    {
        $field = $this->makeField();
        $this->assertInstanceOf(PublicationFieldType::class, $field);

        $this->assertSame(PublicationFieldTypes::String, $field->type);
        $this->assertSame('test', $field->name);
        $this->assertSame('1', $field->min);
        $this->assertSame('10', $field->max);
    }

    public function test_from_array_method()
    {
        $field = PublicationFieldType::fromArray([
            'type' => 'string',
            'name' => 'test',
            'min'  => '1',
            'max'  => '10',
        ]);

        $this->assertInstanceOf(PublicationFieldType::class, $field);

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

    public function test_null_range_values_are_cast_to_empty_string()
    {
        $field = new PublicationFieldType('string', 'test', null, null);
        $this->assertSame('', $field->min);
        $this->assertSame('', $field->max);
    }

    public function test_max_value_cannot_be_less_than_min_value()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'max' value cannot be less than the 'min' value.");

        new PublicationFieldType('string', 'test', '10', '1');
    }

    public function test_integers_can_be_added_as_strings()
    {
        $field = new PublicationFieldType('string', 'test', '1', '10');
        $this->assertSame('1', $field->min);
        $this->assertSame('10', $field->max);
    }

    public function test_type_must_be_valid()
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"invalid" is not a valid backing value for enum "Hyde\Framework\Features\Publications\Concerns\PublicationFieldTypes"');

        new PublicationFieldType('invalid', 'test', '1', '10');
    }

    public function test_type_input_is_case_insensitive()
    {
        $field = new PublicationFieldType('STRING', 'test', '1', '10');
        $this->assertSame(PublicationFieldTypes::String, $field->type);
    }

    public function test_name_gets_stored_as_kebab_case()
    {
        $field = new PublicationFieldType('string', 'Test Field', '1', '10');
        $this->assertSame('test-field', $field->name);
    }

    public function test_validate_input_against_rules()
    {
        $this->markTestIncomplete('TODO: Implement this method.');
    }

    protected function makeField(): PublicationFieldType
    {
        return new PublicationFieldType('string', 'test', 1, '10');
    }
}
