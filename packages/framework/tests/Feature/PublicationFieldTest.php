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
    }

    public function test_from_array_method()
    {
        $field = PublicationField::fromArray([
            'type' => 'string',
            'name' => 'test',
        ]);

        $this->assertInstanceOf(PublicationField::class, $field);

        $this->assertSame(PublicationFieldTypes::String, $field->type);
        $this->assertSame('test', $field->name);
    }

    public function test_can_get_field_as_array()
    {
        $this->assertSame([
            'type' => 'string',
            'name' => 'test',
            'tagGroup' => null,
        ], $this->makeField()->toArray());
    }

    public function test_can_encode_field_as_json()
    {
        $this->assertSame('{"type":"string","name":"test","tagGroup":null}', json_encode($this->makeField()));
    }

    public function test_can_construct_type_using_enum_case()
    {
        $field1 = new PublicationField(PublicationFieldTypes::String, 'test');
        $this->assertSame(PublicationFieldTypes::String, $field1->type);

        $field2 = new PublicationField('string', 'test');
        $this->assertSame(PublicationFieldTypes::String, $field2->type);

        $this->assertEquals($field1, $field2);
    }

    public function test_type_must_be_valid()
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"invalid" is not a valid backing value for enum "'.PublicationFieldTypes::class.'"');

        new PublicationField('invalid', 'test');
    }

    public function test_type_input_is_case_insensitive()
    {
        $field = new PublicationField('STRING', 'test');
        $this->assertSame(PublicationFieldTypes::String, $field->type);
    }

    public function test_name_gets_stored_as_kebab_case()
    {
        $field = new PublicationField('string', 'Test Field');
        $this->assertSame('test-field', $field->name);
    }

    public function test_validate_input_against_rules()
    {
        $this->markTestIncomplete('TODO: Implement this method.');
    }

    protected function makeField(): PublicationField
    {
        return new PublicationField('string', 'test');
    }
}
