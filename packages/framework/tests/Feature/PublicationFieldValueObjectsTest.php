<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use DateTime;
use Exception;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\BooleanField;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\DatetimeField;
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
        $options = ['true', 'false', '0', '1'];

        foreach ($options as $option) {
            $this->assertInstanceOf(BooleanField::class, (new BooleanField($option)));
        }
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
}

class TestValue extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::String;

    protected static function parseInput(string $input): string
    {
        return $input;
    }
}
