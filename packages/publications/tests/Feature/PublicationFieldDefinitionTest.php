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
    public function testCanInstantiateClass()
    {
        $field = new PublicationFieldDefinition('string', 'test');
        $this->assertInstanceOf(PublicationFieldDefinition::class, $field);

        $this->assertSame(PublicationFieldTypes::String, $field->type);
        $this->assertSame('test', $field->name);
    }

    public function testFromArrayMethod()
    {
        $field = PublicationFieldDefinition::fromArray([
            'type' => 'string',
            'name' => 'test',
        ]);

        $this->assertInstanceOf(PublicationFieldDefinition::class, $field);

        $this->assertSame(PublicationFieldTypes::String, $field->type);
        $this->assertSame('test', $field->name);
    }

    public function testCanGetFieldAsArray()
    {
        $this->assertSame([
            'type' => 'string',
            'name' => 'test',
        ], (new PublicationFieldDefinition('string', 'test'))->toArray());
    }

    public function testCanGetFieldWithOptionalPropertiesAsArray()
    {
        $this->assertSame([
            'type' => 'string',
            'name' => 'test',
            'rules' => ['required'],
        ], (new PublicationFieldDefinition('string', 'test', ['required']))->toArray());
    }

    public function testCanEncodeFieldAsJson()
    {
        $this->assertSame('{"type":"string","name":"test"}', json_encode(new PublicationFieldDefinition('string', 'test')));
    }

    public function testCanGetFieldWithOptionalPropertiesAsJson()
    {
        $this->assertSame('{"type":"string","name":"test","rules":["required"]}', json_encode(new PublicationFieldDefinition('string',
            'test',
            ['required']
        )));
    }

    public function testCanConstructTypeUsingEnumCase()
    {
        $field1 = new PublicationFieldDefinition(PublicationFieldTypes::String, 'test');
        $this->assertSame(PublicationFieldTypes::String, $field1->type);

        $field2 = new PublicationFieldDefinition('string', 'test');
        $this->assertSame(PublicationFieldTypes::String, $field2->type);

        $this->assertEquals($field1, $field2);
    }

    public function testTypeMustBeValid()
    {
        $this->expectException(ValueError::class);

        new PublicationFieldDefinition('invalid', 'test');
    }

    public function testTypeInputIsCaseInsensitive()
    {
        $field = new PublicationFieldDefinition('STRING', 'test');
        $this->assertSame(PublicationFieldTypes::String, $field->type);
    }

    public function testNameGetsStoredAsKebabCase()
    {
        $field = new PublicationFieldDefinition('string', 'Test Field');
        $this->assertSame('test-field', $field->name);
    }

    public function testGetRules()
    {
        $field = new PublicationFieldDefinition('string', 'test');
        $this->assertSame(['string'], $field->getRules());
    }

    public function testGetRulesWithCustomTypeRules()
    {
        $field = new PublicationFieldDefinition('string', 'test', ['required', 'foo']);
        $this->assertSame(['string', 'required', 'foo'], $field->getRules());
    }
}
