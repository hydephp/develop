<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;
use Illuminate\Validation\ValidationException;
use ValueError;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationField
 */
class PublicationFieldTest extends TestCase
{
    public function test_can_instantiate_class()
    {
        $field = new PublicationField('string', 'test');
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
        ], (new PublicationField('string', 'test'))->toArray());
    }

    public function test_can_get_field_with_optional_properties_as_array()
    {
        $this->assertSame([
            'type' => 'string',
            'name' => 'test',
            'tagGroup' => 'foo',
            'rules' => ['required'],
        ], (new PublicationField('string', 'test', 'foo', ['required']))->toArray());
    }

    public function test_can_encode_field_as_json()
    {
        $this->assertSame('{"type":"string","name":"test"}', json_encode(new PublicationField('string', 'test')));
    }

    public function test_can_get_field_with_optional_properties_as_json()
    {
        $this->assertSame('{"type":"string","name":"test","tagGroup":"foo","rules":["required"]}', json_encode(new PublicationField('string', 'test', 'foo', ['required'])));
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

    public function testValidate()
    {
        $validated = (new PublicationField('string', 'myString'))->validate('foo');
        $this->assertSame(['my-string' => 'foo'], $validated);

        $this->expectValidationException('The my-string must be a string.');
        (new PublicationField('string', 'myString'))->validate(1);
    }

    public function testValidateWithCustomTypeRules()
    {
        $validated = (new PublicationField('string', 'myString', rules: ['min:3']))->validate('foo');
        $this->assertSame(['my-string' => 'foo'], $validated);

        $this->expectValidationException('The my-string must be at least 5 characters.');
        (new PublicationField('string', 'myString', rules: ['min:5']))->validate('foo');
    }

    public function testValidateWithCustomRuleCollection()
    {
        $validated = (new PublicationField('string', 'myString'))->validate('foo', ['min:3']);
        $this->assertSame(['my-string' => 'foo'], $validated);

        $this->expectValidationException('The my-string must be at least 5 characters.');
        (new PublicationField('string', 'myString'))->validate('foo', ['min:5']);
    }

    public function testValidateWithCustomRuleCollectionOverridesDefaultRules()
    {
        $this->expectValidationException('The my-string must be a number.');
        (new PublicationField('string', 'myString'))->validate('foo', ['numeric']);
    }

    public function testValidateMethodAcceptsArrayOfRules()
    {
        $validated = (new PublicationField('string', 'myString'))->validate('foo', ['min:3']);
        $this->assertSame(['my-string' => 'foo'], $validated);
    }

    public function testValidateMethodAcceptsArrayableOfRules()
    {
        $validated = (new PublicationField('string', 'myString'))->validate('foo', collect(['min:3']));
        $this->assertSame(['my-string' => 'foo'], $validated);
    }

    public function testGetRules()
    {
        $rules = (new PublicationField('string', 'myString'))->getValidationRules();
        $this->assertSame(['string'], $rules->toArray());
    }

    public function testGetRulesWithCustomTypeRules()
    {
        $rules = (new PublicationField('string', 'myString', rules: ['foo', 'bar']))->getValidationRules();
        $this->assertSame(['string', 'foo', 'bar'], $rules->toArray());
    }

    public function testGetRulesForArray()
    {
        $rules = (new PublicationField('array', 'myArray'))->getValidationRules();
        $this->assertSame(['array'], $rules->toArray());
    }

    public function testValidateArrayPasses()
    {
        $validated = (new PublicationField('array', 'myArray'))->validate(['foo', 'bar', 'baz']);
        $this->assertSame(['my-array' => ['foo', 'bar', 'baz']], $validated);
    }

    public function testValidateArrayFails()
    {
        $this->expectValidationException('The my-array must be an array.');
        (new PublicationField('array', 'myArray'))->validate('foo');
    }

    public function testGetRulesForDatetime()
    {
        $rules = (new PublicationField('datetime', 'myDatetime'))->getValidationRules();
        $this->assertSame(['date'], $rules->toArray());
    }

    public function testValidateDatetimePasses()
    {
        $validated = (new PublicationField('datetime', 'myDatetime'))->validate('2021-01-01');
        $this->assertSame(['my-datetime' => '2021-01-01'], $validated);
    }

    public function testValidateDatetimeFailsForInvalidType()
    {
        $this->expectValidationException('The my-datetime is not a valid date.');
        (new PublicationField('datetime', 'myDatetime'))->validate('string');
    }

    public function testGetRulesForFloat()
    {
        $rules = (new PublicationField('float', 'myFloat'))->getValidationRules();
        $this->assertSame(['numeric'], $rules->toArray());
    }

    public function testGetRulesForInteger()
    {
        $rules = (new PublicationField('integer', 'myInteger'))->getValidationRules();
        $this->assertSame(['integer', 'numeric'], $rules->toArray());
    }

    public function testGetRulesForString()
    {
        $rules = (new PublicationField('string', 'myString'))->getValidationRules();
        $this->assertSame(['string'], $rules->toArray());
    }

    public function testGetRulesForText()
    {
        $rules = (new PublicationField('text', 'myText'))->getValidationRules();
        $this->assertSame(['string'], $rules->toArray());
    }

    public function testGetRulesForImage()
    {
        $this->directory('_media/foo');
        $this->file('_media/foo/bar.jpg');
        $this->file('_media/foo/baz.png');
        $rules = (new PublicationField('image', 'myImage'))->getValidationRules(publicationType: new PublicationType('foo'));
        $this->assertSame(['in:_media/foo/bar.jpg,_media/foo/baz.png'], $rules->toArray());
    }

    public function testGetRulesForTag()
    {
        $rules = (new PublicationField('tag', 'myTag', tagGroup: 'foo'))->getValidationRules();
        $this->assertSame(['in:'], $rules->toArray());
    }

    public function testGetRulesForUrl()
    {
        $rules = (new PublicationField('url', 'myUrl'))->getValidationRules();
        $this->assertSame(['url'], $rules->toArray());
    }

    protected function expectValidationException(string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
    }
}
