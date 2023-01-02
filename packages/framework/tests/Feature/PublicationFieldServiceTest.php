<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use DateTime;
use Exception;
use Hyde\Framework\Features\Publications\Models\PublicationFields\ArrayField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\BooleanField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\DatetimeField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\FloatField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\ImageField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\IntegerField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\PublicationFieldValue;
use Hyde\Framework\Features\Publications\Models\PublicationFields\StringField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\TagField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\TextField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\UrlField;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\Validation\BooleanRule;
use Hyde\Testing\TestCase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Hyde\Framework\Features\Publications\PublicationFieldService
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\PublicationFieldValue
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\PublicationField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\StringField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\DatetimeField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\BooleanField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\IntegerField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\FloatField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\ArrayField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\TextField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\UrlField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\ImageField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\TagField
 */
class PublicationFieldServiceTest extends TestCase
{
    // Base class tests

    public function testConstruct()
    {
        $this->assertInstanceOf(PublicationFieldTestClass::class, (new PublicationFieldTestClass('foo')));
    }

    public function testGetValue()
    {
        $this->assertSame('foo', (new PublicationFieldTestClass('foo'))->getValue());
    }

    public function testTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::String, PublicationFieldTestClass::TYPE);
    }

    // StringField tests

    public function testStringFieldConstruct()
    {
        $this->assertInstanceOf(StringField::class, (new PublicationFieldValue(PublicationFieldTypes::String, 'foo')));
    }

    public function testStringFieldGetValue()
    {
        $this->assertSame('foo', (new PublicationFieldValue(PublicationFieldTypes::String, 'foo'))->getValue());
    }

    public function testStringFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::String, StringField::TYPE);
    }

    public function testStringFieldToYaml()
    {
        $this->assertSame('foo', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::String, 'foo')));
    }

    public function testStringFieldParsingOptions()
    {
        $this->assertSame('foo', (new PublicationFieldValue(PublicationFieldTypes::String, 'foo'))->getValue());
        $this->assertSame('true', (new PublicationFieldValue(PublicationFieldTypes::String, 'true'))->getValue());
        $this->assertSame('false', (new PublicationFieldValue(PublicationFieldTypes::String, 'false'))->getValue());
        $this->assertSame('null', (new PublicationFieldValue(PublicationFieldTypes::String, 'null'))->getValue());
        $this->assertSame('0', (new PublicationFieldValue(PublicationFieldTypes::String, '0'))->getValue());
        $this->assertSame('1', (new PublicationFieldValue(PublicationFieldTypes::String, '1'))->getValue());
        $this->assertSame('10.5', (new PublicationFieldValue(PublicationFieldTypes::String, '10.5'))->getValue());
        $this->assertSame('-10', (new PublicationFieldValue(PublicationFieldTypes::String, '-10'))->getValue());
    }

    // DatetimeField tests

    public function testDatetimeFieldConstruct()
    {
        $this->assertInstanceOf(DateTime::class, (new DateTime('2023-01-01')));
    }

    public function testDatetimeFieldGetValue()
    {
        $this->assertEquals(new DateTime('2023-01-01'), (new PublicationFieldValue(PublicationFieldTypes::Datetime, '2023-01-01'))->getValue());
    }

    public function testDatetimeFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Datetime, DatetimeField::TYPE);
    }

    public function testDatetimeFieldWithInvalidInput()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to parse time string (foo)');
        new PublicationFieldValue(PublicationFieldTypes::Datetime, 'foo');
    }

    public function testDatetimeFieldWithDynamicInput()
    {
        $this->assertInstanceOf(DateTime::class, (new PublicationFieldValue(PublicationFieldTypes::Datetime, 'now'))->getValue());
    }

    public function testDatetimeFieldToYaml()
    {
        $this->assertSame('2023-01-01T00:00:00+00:00', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::Datetime, '2023-01-01')));
    }

    // BooleanField tests

    public function testBooleanFieldConstruct()
    {
        $this->assertInstanceOf(BooleanField::class, (new PublicationFieldValue(PublicationFieldTypes::Boolean, 'true')));
    }

    public function testBooleanFieldGetValue()
    {
        $this->assertSame(true, (new PublicationFieldValue(PublicationFieldTypes::Boolean, 'true'))->getValue());
    }

    public function testBooleanFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Boolean, BooleanField::TYPE);
    }

    public function testBooleanFieldToYaml()
    {
        $this->assertSame('true', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::Boolean, 'true')));
    }

    public function testBooleanFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BooleanField: Unable to parse invalid boolean value \'foo\'');
        new PublicationFieldValue(PublicationFieldTypes::Boolean, 'foo');
    }

    public function testBooleanFieldParsingOptions()
    {
        $this->assertSame(true, (new PublicationFieldValue(PublicationFieldTypes::Boolean, 'true'))->getValue());
        $this->assertSame(true, (new PublicationFieldValue(PublicationFieldTypes::Boolean, '1'))->getValue());
        $this->assertSame(false, (new PublicationFieldValue(PublicationFieldTypes::Boolean, 'false'))->getValue());
        $this->assertSame(false, (new PublicationFieldValue(PublicationFieldTypes::Boolean, '0'))->getValue());
    }

    // IntegerField tests

    public function testIntegerFieldConstruct()
    {
        $this->assertInstanceOf(IntegerField::class, (new PublicationFieldValue(PublicationFieldTypes::Integer, '10')));
    }

    public function testIntegerFieldGetValue()
    {
        $this->assertSame(10, (new PublicationFieldValue(PublicationFieldTypes::Integer, '10'))->getValue());
    }

    public function testIntegerFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Integer, IntegerField::TYPE);
    }

    public function testIntegerFieldToYaml()
    {
        $this->assertSame('10', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::Integer, '10')));
    }

    public function testIntegerFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IntegerField: Unable to parse invalid integer value \'foo\'');
        new PublicationFieldValue(PublicationFieldTypes::Integer, 'foo');
    }

    public function testIntegerFieldParsingOptions()
    {
        $this->assertSame(0, (new PublicationFieldValue(PublicationFieldTypes::Integer, '0'))->getValue());
        $this->assertSame(1, (new PublicationFieldValue(PublicationFieldTypes::Integer, '1'))->getValue());
        $this->assertSame(10, (new PublicationFieldValue(PublicationFieldTypes::Integer, '10'))->getValue());
        $this->assertSame(10, (new PublicationFieldValue(PublicationFieldTypes::Integer, '10.0'))->getValue());
        $this->assertSame(10, (new PublicationFieldValue(PublicationFieldTypes::Integer, '10.5'))->getValue());
        $this->assertSame(10, (new PublicationFieldValue(PublicationFieldTypes::Integer, '10.9'))->getValue());
        $this->assertSame(100, (new PublicationFieldValue(PublicationFieldTypes::Integer, '1E2'))->getValue());
        $this->assertSame(-10, (new PublicationFieldValue(PublicationFieldTypes::Integer, '-10'))->getValue());
    }

    // FloatField tests

    public function testFloatFieldConstruct()
    {
        $this->assertInstanceOf(FloatField::class, (new PublicationFieldValue(PublicationFieldTypes::Float, '10')));
    }

    public function testFloatFieldGetValue()
    {
        $this->assertSame(10.0, (new PublicationFieldValue(PublicationFieldTypes::Float, '10'))->getValue());
    }

    public function testFloatFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Float, FloatField::TYPE);
    }

    public function testFloatFieldToYaml()
    {
        $this->assertSame('10.0', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::Float, '10')));
    }

    public function testFloatFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FloatField: Unable to parse invalid float value \'foo\'');
        new PublicationFieldValue(PublicationFieldTypes::Float, 'foo');
    }

    public function testFloatFieldParsingOptions()
    {
        $this->assertSame(0.0, (new PublicationFieldValue(PublicationFieldTypes::Float, '0'))->getValue());
        $this->assertSame(1.0, (new PublicationFieldValue(PublicationFieldTypes::Float, '1'))->getValue());
        $this->assertSame(10.0, (new PublicationFieldValue(PublicationFieldTypes::Float, '10'))->getValue());
        $this->assertSame(10.0, (new PublicationFieldValue(PublicationFieldTypes::Float, '10.0'))->getValue());
        $this->assertSame(10.5, (new PublicationFieldValue(PublicationFieldTypes::Float, '10.5'))->getValue());
        $this->assertSame(10.9, (new PublicationFieldValue(PublicationFieldTypes::Float, '10.9'))->getValue());
        $this->assertSame(100.0, (new PublicationFieldValue(PublicationFieldTypes::Float, '1E2'))->getValue());
        $this->assertSame(-10.0, (new PublicationFieldValue(PublicationFieldTypes::Float, '-10'))->getValue());
    }

    // ArrayField tests

    public function testArrayFieldConstruct()
    {
        $this->assertInstanceOf(ArrayField::class, (new PublicationFieldValue(PublicationFieldTypes::Array, 'foo')));
    }

    public function testArrayFieldGetValue()
    {
        $this->assertSame(['foo'], (new PublicationFieldValue(PublicationFieldTypes::Array, 'foo'))->getValue());
    }

    public function testArrayFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Array, ArrayField::TYPE);
    }

    public function testArrayFieldToYaml()
    {
        $this->assertSame("- foo\n", $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::Array, 'foo')));
    }

    public function testArrayFieldWithArrayInput()
    {
        $this->assertSame(['foo'], (new PublicationFieldValue(PublicationFieldTypes::Array, ['foo']))->getValue());
    }

    public function testArrayFieldParsingOptions()
    {
        $this->assertSame(['foo'], (new PublicationFieldValue(PublicationFieldTypes::Array, 'foo'))->getValue());
        $this->assertSame(['true'], (new PublicationFieldValue(PublicationFieldTypes::Array, 'true'))->getValue());
        $this->assertSame(['false'], (new PublicationFieldValue(PublicationFieldTypes::Array, 'false'))->getValue());
        $this->assertSame(['null'], (new PublicationFieldValue(PublicationFieldTypes::Array, 'null'))->getValue());
        $this->assertSame(['0'], (new PublicationFieldValue(PublicationFieldTypes::Array, '0'))->getValue());
        $this->assertSame(['1'], (new PublicationFieldValue(PublicationFieldTypes::Array, '1'))->getValue());
        $this->assertSame(['10.5'], (new PublicationFieldValue(PublicationFieldTypes::Array, '10.5'))->getValue());
        $this->assertSame(['-10'], (new PublicationFieldValue(PublicationFieldTypes::Array, '-10'))->getValue());
    }

    // TextField tests

    public function testTextFieldConstruct()
    {
        $this->assertInstanceOf(TextField::class, (new PublicationFieldValue(PublicationFieldTypes::Text, 'foo')));
    }

    public function testTextFieldGetValue()
    {
        $this->assertSame('foo', (new PublicationFieldValue(PublicationFieldTypes::Text, 'foo'))->getValue());
    }

    public function testTextFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Text, TextField::TYPE);
    }

    public function testTextFieldToYaml()
    {
        $this->assertSame('foo', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::Text, 'foo')));
        // Note that this does not use the same flags as the creator action, because that's out of scope for this test.
    }

    public function testTextFieldParsingOptions()
    {
        $this->assertSame('foo', (new PublicationFieldValue(PublicationFieldTypes::Text, 'foo'))->getValue());
        $this->assertSame('true', (new PublicationFieldValue(PublicationFieldTypes::Text, 'true'))->getValue());
        $this->assertSame('false', (new PublicationFieldValue(PublicationFieldTypes::Text, 'false'))->getValue());
        $this->assertSame('null', (new PublicationFieldValue(PublicationFieldTypes::Text, 'null'))->getValue());
        $this->assertSame('0', (new PublicationFieldValue(PublicationFieldTypes::Text, '0'))->getValue());
        $this->assertSame('1', (new PublicationFieldValue(PublicationFieldTypes::Text, '1'))->getValue());
        $this->assertSame('10.5', (new PublicationFieldValue(PublicationFieldTypes::Text, '10.5'))->getValue());
        $this->assertSame('-10', (new PublicationFieldValue(PublicationFieldTypes::Text, '-10'))->getValue());
        $this->assertSame("foo\nbar\n", (new PublicationFieldValue(PublicationFieldTypes::Text, "foo\nbar"))->getValue());
        $this->assertSame("foo\nbar\n", (new PublicationFieldValue(PublicationFieldTypes::Text, "foo\nbar\n"))->getValue());
        $this->assertSame("foo\nbar\nbaz\n", (new PublicationFieldValue(PublicationFieldTypes::Text, "foo\nbar\nbaz"))->getValue());
        $this->assertSame("foo\nbar\nbaz\n", (new PublicationFieldValue(PublicationFieldTypes::Text, "foo\nbar\nbaz\n"))->getValue());
        $this->assertSame("foo\r\nbar\r\nbaz\n", (new PublicationFieldValue(PublicationFieldTypes::Text, "foo\r\nbar\r\nbaz\r\n"))->getValue());
    }

    // UrlField tests

    public function testUrlFieldConstruct()
    {
        $this->assertInstanceOf(UrlField::class, (new PublicationFieldValue(PublicationFieldTypes::Url, 'https://example.com')));
    }

    public function testUrlFieldGetValue()
    {
        $this->assertSame('https://example.com', (new PublicationFieldValue(PublicationFieldTypes::Url, 'https://example.com'))->getValue());
    }

    public function testUrlFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Url, UrlField::TYPE);
    }

    public function testUrlFieldToYaml()
    {
        $this->assertSame('\'https://example.com\'', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::Url, 'https://example.com')));
    }

    public function testUrlFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UrlField: Unable to parse invalid url value \'foo\'');
        new PublicationFieldValue(PublicationFieldTypes::Url, 'foo');
    }

    // ImageField tests

    public function testImageFieldConstruct()
    {
        $this->assertInstanceOf(ImageField::class, (new PublicationFieldValue(PublicationFieldTypes::Image, 'foo')));
    }

    public function testImageFieldGetValue()
    {
        $this->assertSame('foo', (new PublicationFieldValue(PublicationFieldTypes::Image, 'foo'))->getValue());
    }

    public function testImageFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Image, ImageField::TYPE);
    }

    public function testImageFieldToYaml()
    {
        $this->assertSame('foo', $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::Image, 'foo')));
    }

    // TagField tests

    public function testTagFieldConstruct()
    {
        $this->assertInstanceOf(TagField::class, (new PublicationFieldValue(PublicationFieldTypes::Tag, 'foo')));
    }

    public function testTagFieldGetValue()
    {
        $this->assertSame(['foo'], (new PublicationFieldValue(PublicationFieldTypes::Tag, 'foo'))->getValue());
    }

    public function testTagFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Tag, TagField::TYPE);
    }

    public function testTagFieldToYaml()
    {
        $this->assertSame("- foo\n", $this->getYaml(new PublicationFieldValue(PublicationFieldTypes::Tag, 'foo')));
    }

    public function testTagFieldWithArrayInput()
    {
        $this->assertSame(['foo'], (new PublicationFieldValue(PublicationFieldTypes::Tag, ['foo']))->getValue());
    }

    public function testTagFieldParsingOptions()
    {
        $this->assertSame(['foo'], (new PublicationFieldValue(PublicationFieldTypes::Tag, 'foo'))->getValue());
        $this->assertSame(['true'], (new PublicationFieldValue(PublicationFieldTypes::Tag, 'true'))->getValue());
        $this->assertSame(['false'], (new PublicationFieldValue(PublicationFieldTypes::Tag, 'false'))->getValue());
        $this->assertSame(['null'], (new PublicationFieldValue(PublicationFieldTypes::Tag, 'null'))->getValue());
        $this->assertSame(['0'], (new PublicationFieldValue(PublicationFieldTypes::Tag, '0'))->getValue());
        $this->assertSame(['1'], (new PublicationFieldValue(PublicationFieldTypes::Tag, '1'))->getValue());
        $this->assertSame(['10.5'], (new PublicationFieldValue(PublicationFieldTypes::Tag, '10.5'))->getValue());
        $this->assertSame(['-10'], (new PublicationFieldValue(PublicationFieldTypes::Tag, '-10'))->getValue());
    }

    // Additional tests

    public function testAllTypesHaveAValueClass()
    {
        $namespace = Str::beforeLast(PublicationField::class, '\\');
        foreach (PublicationFieldTypes::names() as $type) {
            $this->assertTrue(
                class_exists("$namespace\\{$type}Field"),
                "Missing value class for type $type"
            );
        }
    }

    public function testAllTypesCanBeResolvedByTheServiceContainer()
    {
        $namespace = Str::beforeLast(PublicationField::class, '\\');
        foreach (PublicationFieldTypes::names() as $type) {
            $this->assertInstanceOf(
                "$namespace\\{$type}Field",
                app()->make("$namespace\\{$type}Field")
            );
        }
    }

    public function testDefaultValidationRules()
    {
        $expected = [
            'string' => ['string'],
            'datetime' => ['date'],
            'boolean' => [new BooleanRule],
            'integer' => ['integer', 'numeric'],
            'float' => ['numeric'],
            'image' => [],
            'array' => ['array'],
            'text' => ['string'],
            'url' => ['url'],
            'tag' => [],
        ];

        foreach ($expected as $type => $rules) {
            $this->assertEquals($rules, PublicationFieldTypes::from($type)->rules());
        }
    }

    // Testing helper methods

    protected function getYaml(PublicationField $field): string
    {
        return Yaml::dump($field->getValue());
    }
}

class PublicationFieldTestClass extends PublicationField
{
    public const TYPE = PublicationFieldTypes::String;
}
