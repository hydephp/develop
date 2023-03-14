<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use DateTime;
use Exception;
use Hyde\Publications\Models\PublicationFieldValue;
use Hyde\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Hyde\Publications\Models\PublicationFieldValue
 */
class PublicationFieldValueTest extends TestCase
{
    // Base tests

    public function testConstruct()
    {
        $this->assertInstanceOf(PublicationFieldValue::class,
            new PublicationFieldValue(PublicationFieldTypes::String, 'foo')
        );
    }

    public function testGetType()
    {
        $this->assertSame(PublicationFieldTypes::String,
            (new PublicationFieldValue(PublicationFieldTypes::String, 'foo'))->getType()
        );
    }

    public function testGetValue()
    {
        $this->assertSame('foo', (new PublicationFieldValue(PublicationFieldTypes::String, 'foo'))->getValue());
    }

    public function testType()
    {
        $field = new PublicationFieldValue(PublicationFieldTypes::String, 'foo');
        $this->assertSame(PublicationFieldTypes::String, $field->type);
        $this->assertSame($field->type, $field->getType());
    }

    // StringField tests

    public function testStringFieldConstruct()
    {
        $field = $this->makeFieldType('string', 'foo');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::String, $field->type);
    }

    public function testStringFieldGetValue()
    {
        $this->assertSame('foo', $this->makeFieldType('string', 'foo')->getValue());
    }

    public function testStringFieldToYaml()
    {
        $this->assertSame('foo', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('string'), 'foo')));
    }

    public function testStringFieldParsingOptions()
    {
        $this->assertSame('foo', $this->makeFieldType('string', 'foo')->getValue());
        $this->assertSame('true', $this->makeFieldType('string', 'true')->getValue());
        $this->assertSame('false', $this->makeFieldType('string', 'false')->getValue());
        $this->assertSame('null', $this->makeFieldType('string', 'null')->getValue());
        $this->assertSame('0', $this->makeFieldType('string', '0')->getValue());
        $this->assertSame('1', $this->makeFieldType('string', '1')->getValue());
        $this->assertSame('10.5', $this->makeFieldType('string', '10.5')->getValue());
        $this->assertSame('-10', $this->makeFieldType('string', '-10')->getValue());
    }

    // DatetimeField tests

    public function testDatetimeFieldConstruct()
    {
        $field = $this->makeFieldType('datetime', '2023-01-01');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::Datetime, $field->type);
    }

    public function testDatetimeFieldGetValue()
    {
        $this->assertEquals(new DateTime('2023-01-01'), $this->makeFieldType('datetime', '2023-01-01')->getValue());
    }

    public function testDatetimeFieldWithInvalidInput()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to parse time string (foo)');
        new PublicationFieldValue(PublicationFieldTypes::from('datetime'), 'foo');
    }

    public function testDatetimeFieldWithDynamicInput()
    {
        $field = $this->makeFieldType('datetime', 'now')->getValue();

        $this->assertInstanceOf(DateTime::class, $field);
    }

    public function testDatetimeFieldToYaml()
    {
        $this->assertSame('2023-01-01T00:00:00+00:00', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('datetime'), '2023-01-01')));
    }

    // BooleanField tests

    public function testBooleanFieldConstruct()
    {
        $field = $this->makeFieldType('boolean', 'true');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::Boolean, $field->type);
    }

    public function testBooleanFieldGetValue()
    {
        $this->assertSame(true, $this->makeFieldType('boolean', 'true')->getValue());
    }

    public function testBooleanFieldToYaml()
    {
        $this->assertSame('true', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('boolean'), 'true')));
    }

    public function testBooleanFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BooleanField: Unable to parse invalid boolean value \'foo\'');
        new PublicationFieldValue(PublicationFieldTypes::from('boolean'), 'foo');
    }

    public function testBooleanFieldParsingOptions()
    {
        $this->assertSame(true, $this->makeFieldType('boolean', 'true')->getValue());
        $this->assertSame(true, $this->makeFieldType('boolean', '1')->getValue());
        $this->assertSame(false, $this->makeFieldType('boolean', 'false')->getValue());
        $this->assertSame(false, $this->makeFieldType('boolean', '0')->getValue());
    }

    // IntegerField tests

    public function testIntegerFieldConstruct()
    {
        $field = $this->makeFieldType('integer', '10');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::Integer, $field->type);
    }

    public function testIntegerFieldGetValue()
    {
        $this->assertSame(10, $this->makeFieldType('integer', '10')->getValue());
    }

    public function testIntegerFieldToYaml()
    {
        $this->assertSame('10', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('integer'), '10')));
    }

    public function testIntegerFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IntegerField: Unable to parse invalid integer value \'foo\'');
        new PublicationFieldValue(PublicationFieldTypes::from('integer'), 'foo');
    }

    public function testIntegerFieldParsingOptions()
    {
        $this->assertSame(0, $this->makeFieldType('integer', '0')->getValue());
        $this->assertSame(1, $this->makeFieldType('integer', '1')->getValue());
        $this->assertSame(10, $this->makeFieldType('integer', '10')->getValue());
        $this->assertSame(10, $this->makeFieldType('integer', '10.0')->getValue());
        $this->assertSame(10, $this->makeFieldType('integer', '10.5')->getValue());
        $this->assertSame(10, $this->makeFieldType('integer', '10.9')->getValue());
        $this->assertSame(100, $this->makeFieldType('integer', '1E2')->getValue());
        $this->assertSame(-10, $this->makeFieldType('integer', '-10')->getValue());
    }

    // FloatField tests

    public function testFloatFieldConstruct()
    {
        $field = $this->makeFieldType('float', '10');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::Float, $field->type);
    }

    public function testFloatFieldGetValue()
    {
        $this->assertSame(10.0, $this->makeFieldType('float', '10')->getValue());
    }

    public function testFloatFieldToYaml()
    {
        $this->assertSame('10.0', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('float'), '10')));
    }

    public function testFloatFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FloatField: Unable to parse invalid float value \'foo\'');
        new PublicationFieldValue(PublicationFieldTypes::from('float'), 'foo');
    }

    public function testFloatFieldParsingOptions()
    {
        $this->assertSame(0.0, $this->makeFieldType('float', '0')->getValue());
        $this->assertSame(1.0, $this->makeFieldType('float', '1')->getValue());
        $this->assertSame(10.0, $this->makeFieldType('float', '10')->getValue());
        $this->assertSame(10.0, $this->makeFieldType('float', '10.0')->getValue());
        $this->assertSame(10.5, $this->makeFieldType('float', '10.5')->getValue());
        $this->assertSame(10.9, $this->makeFieldType('float', '10.9')->getValue());
        $this->assertSame(100.0, $this->makeFieldType('float', '1E2')->getValue());
        $this->assertSame(-10.0, $this->makeFieldType('float', '-10')->getValue());
    }

    // ArrayField tests

    public function testArrayFieldConstruct()
    {
        $field = $this->makeFieldType('array', 'foo');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::Array, $field->type);
    }

    public function testArrayFieldGetValue()
    {
        $this->assertSame(['foo'], $this->makeFieldType('array', 'foo')->getValue());
    }

    public function testArrayFieldToYaml()
    {
        $this->assertSame("- foo\n", $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('array'), 'foo')));
    }

    public function testArrayFieldWithArrayInput()
    {
        $this->assertSame(['foo'], $this->makeFieldType('array', ['foo'])->getValue());
    }

    public function testArrayFieldParsingOptions()
    {
        $this->assertSame(['foo'], $this->makeFieldType('array', 'foo')->getValue());
        $this->assertSame(['true'], $this->makeFieldType('array', 'true')->getValue());
        $this->assertSame(['false'], $this->makeFieldType('array', 'false')->getValue());
        $this->assertSame(['null'], $this->makeFieldType('array', 'null')->getValue());
        $this->assertSame(['0'], $this->makeFieldType('array', '0')->getValue());
        $this->assertSame(['1'], $this->makeFieldType('array', '1')->getValue());
        $this->assertSame(['10.5'], $this->makeFieldType('array', '10.5')->getValue());
        $this->assertSame(['-10'], $this->makeFieldType('array', '-10')->getValue());
    }

    // TextField tests

    public function testTextFieldConstruct()
    {
        $field = $this->makeFieldType('text', 'foo');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::Text, $field->type);
    }

    public function testTextFieldGetValue()
    {
        $this->assertSame('foo', $this->makeFieldType('text', 'foo')->getValue());
    }

    public function testTextFieldToYaml()
    {
        $this->assertSame('foo', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('text'), 'foo')));
        // Note that this does not use the same flags as the creator action, because that's out of scope for this test.
    }

    public function testTextFieldParsingOptions()
    {
        $this->assertSame('foo', $this->makeFieldType('text', 'foo')->getValue());
        $this->assertSame('true', $this->makeFieldType('text', 'true')->getValue());
        $this->assertSame('false', $this->makeFieldType('text', 'false')->getValue());
        $this->assertSame('null', $this->makeFieldType('text', 'null')->getValue());
        $this->assertSame('0', $this->makeFieldType('text', '0')->getValue());
        $this->assertSame('1', $this->makeFieldType('text', '1')->getValue());
        $this->assertSame('10.5', $this->makeFieldType('text', '10.5')->getValue());
        $this->assertSame('-10', $this->makeFieldType('text', '-10')->getValue());
        $this->assertSame("foo\nbar\n", $this->makeFieldType('text', "foo\nbar")->getValue());
        $this->assertSame("foo\nbar\n", $this->makeFieldType('text', "foo\nbar\n")->getValue());
        $this->assertSame("foo\nbar\nbaz\n", $this->makeFieldType('text', "foo\nbar\nbaz")->getValue());
        $this->assertSame("foo\nbar\nbaz\n", $this->makeFieldType('text', "foo\nbar\nbaz\n")->getValue());
        $this->assertSame("foo\r\nbar\r\nbaz\n", $this->makeFieldType('text', "foo\r\nbar\r\nbaz\r\n")->getValue());
    }

    // UrlField tests

    public function testUrlFieldConstruct()
    {
        $field = $this->makeFieldType('url', 'https://example.com');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::from('url'), $field->type);
    }

    public function testUrlFieldGetValue()
    {
        $this->assertSame('https://example.com', $this->makeFieldType('url', 'https://example.com')->getValue());
    }

    public function testUrlFieldToYaml()
    {
        $this->assertSame('\'https://example.com\'', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('url'), 'https://example.com')));
    }

    public function testUrlFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UrlField: Unable to parse invalid url value \'foo\'');
        new PublicationFieldValue(PublicationFieldTypes::from('url'), 'foo');
    }

    // MediaField tests

    public function testMediaFieldConstruct()
    {
        $field = $this->makeFieldType('media', 'foo');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::Media, $field->type);
    }

    public function testMediaFieldGetValue()
    {
        $this->assertSame('foo', $this->makeFieldType('media', 'foo')->getValue());
    }

    public function testMediaFieldToYaml()
    {
        $this->assertSame('foo', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('media'), 'foo')));
    }

    // TagField tests

    public function testTagFieldConstruct()
    {
        $field = $this->makeFieldType('tag', 'foo');

        $this->assertInstanceOf(PublicationFieldValue::class, $field);
        $this->assertSame(PublicationFieldTypes::Tag, $field->type);
    }

    public function testTagFieldGetValue()
    {
        $this->assertSame(['foo'], $this->makeFieldType('tag', 'foo')->getValue());
    }

    public function testTagFieldToYaml()
    {
        $this->assertSame("- foo\n", $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::from('tag'), 'foo')));
    }

    public function testTagFieldWithArrayInput()
    {
        $this->assertSame(['foo'], $this->makeFieldType('tag', ['foo'])->getValue());
    }

    public function testTagFieldParsingOptions()
    {
        $this->assertSame(['foo'], $this->makeFieldType('tag', 'foo')->getValue());
        $this->assertSame(['true'], $this->makeFieldType('tag', 'true')->getValue());
        $this->assertSame(['false'], $this->makeFieldType('tag', 'false')->getValue());
        $this->assertSame(['null'], $this->makeFieldType('tag', 'null')->getValue());
        $this->assertSame(['0'], $this->makeFieldType('tag', '0')->getValue());
        $this->assertSame(['1'], $this->makeFieldType('tag', '1')->getValue());
        $this->assertSame(['10.5'], $this->makeFieldType('tag', '10.5')->getValue());
        $this->assertSame(['-10'], $this->makeFieldType('tag', '-10')->getValue());
    }

    // Additional tests

    public function testDefaultValidationRules()
    {
        $expected = [
            'string' => ['string'],
            'datetime' => ['date'],
            'boolean' => ['boolean'],
            'integer' => ['integer'],
            'float' => ['numeric'],
            'media' => ['string'],
            'array' => ['array'],
            'text' => ['string'],
            'url' => ['url'],
            'tag' => [],
        ];

        foreach ($expected as $type => $rules) {
            $this->assertSame($rules, PublicationFieldTypes::from($type)->rules());
        }
    }

    // Testing helper methods

    protected function getYaml(PublicationFieldValue $field): string
    {
        return Yaml::dump($field->getValue());
    }

    protected function makeFieldType(string $type, string|array $value): PublicationFieldValue
    {
        return new PublicationFieldValue(PublicationFieldTypes::from($type), $value);
    }
}
