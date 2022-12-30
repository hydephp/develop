<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use DateTime;
use Exception;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\ArrayField;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\BooleanField;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\DatetimeField;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\FloatField;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\IntegerField;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\StringField;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\StringField
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\DatetimeField
 */
class PublicationFieldValueObjectsTest extends TestCase
{
    // Base class tests

    public function testConstruct()
    {
        $this->assertInstanceOf(TestValue::class, (new TestValue('foo')));
    }

    public function testGetValue()
    {
        $this->assertSame('foo', (new TestValue('foo'))->getValue());
    }

    public function testTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::String, TestValue::TYPE);
    }

    public function testGetType()
    {
        $this->assertSame(TestValue::TYPE, TestValue::getType());
    }

    // StringField tests

    public function testStringFieldConstruct()
    {
        $this->assertInstanceOf(StringField::class, (new StringField('foo')));
    }

    public function testStringFieldGetValue()
    {
        $this->assertSame('foo', (new StringField('foo'))->getValue());
    }

    public function testStringFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::String, StringField::TYPE);
    }

    public function testStringFieldGetType()
    {
        $this->assertSame(StringField::TYPE, StringField::getType());
    }

    public function testStringFieldToYaml()
    {
        $this->assertSame('foo', Yaml::dump((new StringField('foo'))->getValue()));
    }

    public function testStringParsingOptions()
    {
        $this->assertSame('foo', (new StringField('foo'))->getValue());
        $this->assertSame('true', (new StringField('true'))->getValue());
        $this->assertSame('false', (new StringField('false'))->getValue());
        $this->assertSame('null', (new StringField('null'))->getValue());
        $this->assertSame('0', (new StringField('0'))->getValue());
        $this->assertSame('1', (new StringField('1'))->getValue());
        $this->assertSame('10.5', (new StringField('10.5'))->getValue());
        $this->assertSame('-10', (new StringField('-10'))->getValue());
    }

    // DatetimeField tests

    public function testDatetimeFieldConstruct()
    {
        $this->assertInstanceOf(DateTime::class, (new DateTime('2023-01-01')));
    }

    public function testDatetimeFieldGetValue()
    {
        $this->assertEquals(new DateTime('2023-01-01'), (new DatetimeField('2023-01-01'))->getValue());
    }

    public function testDatetimeFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Datetime, DatetimeField::TYPE);
    }

    public function testDatetimeFieldGetType()
    {
        $this->assertSame(DatetimeField::TYPE, DatetimeField::getType());
    }

    public function testDatetimeFieldWithInvalidInput()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to parse time string (foo)');
        new DatetimeField('foo');
    }

    public function testDatetimeFieldWithDynamicInput()
    {
        $this->assertInstanceOf(DateTime::class, (new DatetimeField('now'))->getValue());
    }

    public function testDatetimeFieldToYaml()
    {
        $this->assertSame('2023-01-01T00:00:00+00:00', Yaml::dump((new DatetimeField('2023-01-01'))->getValue()));
    }

    // BooleanField tests

    public function testBooleanFieldConstruct()
    {
        $this->assertInstanceOf(BooleanField::class, (new BooleanField('true')));
    }

    public function testBooleanFieldGetValue()
    {
        $this->assertSame(true, (new BooleanField('true'))->getValue());
    }

    public function testBooleanFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Boolean, BooleanField::TYPE);
    }

    public function testBooleanFieldGetType()
    {
        $this->assertSame(BooleanField::TYPE, BooleanField::getType());
    }

    public function testBooleanFieldToYaml()
    {
        $this->assertSame('true', Yaml::dump((new BooleanField('true'))->getValue()));
    }

    public function testBooleanFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BooleanField: Unable to parse invalid boolean value \'foo\'');
        new BooleanField('foo');
    }

    public function testBooleanParsingOptions()
    {
        $this->assertSame(true, (new BooleanField('true'))->getValue());
        $this->assertSame(true, (new BooleanField('1'))->getValue());
        $this->assertSame(false, (new BooleanField('false'))->getValue());
        $this->assertSame(false, (new BooleanField('0'))->getValue());
    }

    // IntegerField tests

    public function testIntegerFieldConstruct()
    {
        $this->assertInstanceOf(IntegerField::class, (new IntegerField('10')));
    }

    public function testIntegerFieldGetValue()
    {
        $this->assertSame(10, (new IntegerField('10'))->getValue());
    }

    public function testIntegerFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Integer, IntegerField::TYPE);
    }

    public function testIntegerFieldGetType()
    {
        $this->assertSame(IntegerField::TYPE, IntegerField::getType());
    }

    public function testIntegerFieldToYaml()
    {
        $this->assertSame('10', Yaml::dump((new IntegerField('10'))->getValue()));
    }

    public function testIntegerFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IntegerField: Unable to parse invalid integer value \'foo\'');
        new IntegerField('foo');
    }

    public function testIntegerParsingOptions()
    {
        $this->assertSame(0, (new IntegerField('0'))->getValue());
        $this->assertSame(1, (new IntegerField('1'))->getValue());
        $this->assertSame(10, (new IntegerField('10'))->getValue());
        $this->assertSame(10, (new IntegerField('10.0'))->getValue());
        $this->assertSame(10, (new IntegerField('10.5'))->getValue());
        $this->assertSame(10, (new IntegerField('10.9'))->getValue());
        $this->assertSame(100, (new IntegerField('1E2'))->getValue());
        $this->assertSame(-10, (new IntegerField('-10'))->getValue());
    }

    // FloatField tests

    public function testFloatFieldConstruct()
    {
        $this->assertInstanceOf(FloatField::class, (new FloatField('10')));
    }

    public function testFloatFieldGetValue()
    {
        $this->assertSame(10.0, (new FloatField('10'))->getValue());
    }

    public function testFloatFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Float, FloatField::TYPE);
    }

    public function testFloatFieldGetType()
    {
        $this->assertSame(FloatField::TYPE, FloatField::getType());
    }

    public function testFloatFieldToYaml()
    {
        $this->assertSame('10.0', Yaml::dump((new FloatField('10'))->getValue()));
    }

    public function testFloatFieldWithInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FloatField: Unable to parse invalid float value \'foo\'');
        new FloatField('foo');
    }

    public function testFloatParsingOptions()
    {
        $this->assertSame(0.0, (new FloatField('0'))->getValue());
        $this->assertSame(1.0, (new FloatField('1'))->getValue());
        $this->assertSame(10.0, (new FloatField('10'))->getValue());
        $this->assertSame(10.0, (new FloatField('10.0'))->getValue());
        $this->assertSame(10.5, (new FloatField('10.5'))->getValue());
        $this->assertSame(10.9, (new FloatField('10.9'))->getValue());
        $this->assertSame(100.0, (new FloatField('1E2'))->getValue());
        $this->assertSame(-10.0, (new FloatField('-10'))->getValue());
    }

    // ArrayField tests

    public function testArrayFieldConstruct()
    {
        $this->assertInstanceOf(ArrayField::class, (new ArrayField('foo')));
    }

    public function testArrayFieldGetValue()
    {
        $this->assertSame(['foo'], (new ArrayField('foo'))->getValue());
    }

    public function testArrayFieldTypeConstant()
    {
        $this->assertSame(PublicationFieldTypes::Array, ArrayField::TYPE);
    }

    public function testArrayFieldGetType()
    {
        $this->assertSame(ArrayField::TYPE, ArrayField::getType());
    }

    public function testArrayFieldToYaml()
    {
        $this->assertSame("- foo\n", Yaml::dump((new ArrayField('foo'))->getValue()));
    }

    public function testArrayParsingOptions()
    {
        $this->assertSame(['foo'], (new ArrayField('foo'))->getValue());
        $this->assertSame(['true'], (new ArrayField('true'))->getValue());
        $this->assertSame(['false'], (new ArrayField('false'))->getValue());
        $this->assertSame(['null'], (new ArrayField('null'))->getValue());
        $this->assertSame(['0'], (new ArrayField('0'))->getValue());
        $this->assertSame(['1'], (new ArrayField('1'))->getValue());
        $this->assertSame(['10.5'], (new ArrayField('10.5'))->getValue());
        $this->assertSame(['-10'], (new ArrayField('-10'))->getValue());
    }

    public function testArrayParsingWithCommaSeparatedValues()
    {
        $this->assertSame(['foo', 'bar'], (new ArrayField('foo, bar', ArrayField::PARSE_FROM_CSV))->getValue());
    }

    public function testArrayParsingWithNewlineSeparatedValues()
    {
        $this->assertSame(['foo', 'bar'], (new ArrayField("foo\nbar", ArrayField::PARSE_FROM_NEWLINES))->getValue());
    }
}

class TestValue extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::String;

    protected static function parseInput(string $input): string
    {
        return $input;
    }
}
