<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use ValueError;
use Hyde\Testing\TestCase;
use Hyde\Publications\Concerns\PublicationFieldTypes;
use Hyde\Publications\Models\PublicationFieldDefinition;

/**
 * @covers \Hyde\Publications\Models\PublicationFieldDefinition
 */
class PublicationFieldDefinitionTest extends TestCase
{
    public function test_can_instantiate_class()
    {
        $field = new PublicationFieldDefinition('string', 'test');
        $this->assertInstanceOf(PublicationFieldDefinition::class, $field);

        $this->assertSame(PublicationFieldTypes::String, $field->type);
        $this->assertSame('test', $field->name);
    }

    public function test_from_array_method()
    {
        $field = PublicationFieldDefinition::fromArray([
            'type' => 'string',
            'name' => 'test',
        ]);

        $this->assertInstanceOf(PublicationFieldDefinition::class, $field);

        $this->assertSame(PublicationFieldTypes::String, $field->type);
        $this->assertSame('test', $field->name);
    }

    public function test_can_get_field_as_array()
    {
        $this->assertSame([
            'type' => 'string',
            'name' => 'test',
        ], (new PublicationFieldDefinition('string', 'test'))->toArray());
    }

    public function test_can_get_field_with_optional_properties_as_array()
    {
        $this->assertSame([
            'type' => 'string',
            'name' => 'test',
            'rules' => ['required'],
        ], (new PublicationFieldDefinition('string', 'test', ['required']))->toArray());
    }

    public function test_can_encode_field_as_json()
    {
        $this->assertSame('{"type":"string","name":"test"}', json_encode(new PublicationFieldDefinition('string', 'test')));
    }

    public function test_can_get_field_with_optional_properties_as_json()
    {
        $this->assertSame('{"type":"string","name":"test","rules":["required"]}', json_encode(new PublicationFieldDefinition('string',
            'test',
            ['required']
        )));
    }

    public function test_can_construct_type_using_enum_case()
    {
        $field1 = new PublicationFieldDefinition(PublicationFieldTypes::String, 'test');
        $this->assertSame(PublicationFieldTypes::String, $field1->type);

        $field2 = new PublicationFieldDefinition('string', 'test');
        $this->assertSame(PublicationFieldTypes::String, $field2->type);

        $this->assertEquals($field1, $field2);
    }

    public function test_type_must_be_valid()
    {
        $this->expectException(ValueError::class);

        new PublicationFieldDefinition('invalid', 'test');
    }

    public function test_type_input_is_case_insensitive()
    {
        $field = new PublicationFieldDefinition('STRING', 'test');
        $this->assertSame(PublicationFieldTypes::String, $field->type);
    }

    public function test_name_gets_stored_as_kebab_case()
    {
        $field = new PublicationFieldDefinition('string', 'Test Field');
        $this->assertSame('test-field', $field->name);
    }

    public function test_get_rules()
    {
        $field = new PublicationFieldDefinition('string', 'test');
        $this->assertSame(['string'], $field->getRules());
    }

    public function test_get_rules_with_custom_type_rules()
    {
        $field = new PublicationFieldDefinition('string', 'test', ['required', 'foo']);
        $this->assertSame(['string', 'required', 'foo'], $field->getRules());
    }

    public function test_can_construct_with_tag_group()
    {
        $field = new PublicationFieldDefinition('tag', 'test', [], 'myTags');
        $this->assertSame('myTags', $field->tagGroup);
    }

    public function test_can_serialize_tag_group()
    {
        $field = new PublicationFieldDefinition('tag', 'test', [], 'myTags');
        $this->assertSame([
            'type' => 'tag',
            'name' => 'test',
            'tagGroup' => 'myTags',
        ], $field->toArray());

        $this->assertSame('{"type":"tag","name":"test","tagGroup":"myTags"}', json_encode($field));
    }
}
