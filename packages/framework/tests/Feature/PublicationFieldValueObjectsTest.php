<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use DateTime;
use Exception;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\DatetimeField;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\StringField;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;
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
        $this->assertSame('foo', (new TestValue('foo'))->getValue());
    }

    public function testGetValue()
    {
        $this->assertSame('foo', (new TestValue('foo'))->getValue());
    }

    public function testGetType()
    {
        $this->assertSame(TestValue::TYPE, TestValue::getType());
        $this->assertSame(PublicationFieldTypes::String, TestValue::getType());
    }

    // StringField tests

    public function testStringFieldConstruct()
    {
        $this->assertSame('foo', (new StringField('foo'))->getValue());
    }

    public function testStringFieldGetValue()
    {
        $this->assertSame('foo', (new StringField('foo'))->getValue());
    }

    public function testStringFieldGetType()
    {
        $this->assertSame(StringField::TYPE, StringField::getType());
        $this->assertSame(PublicationFieldTypes::String, StringField::getType());
    }

    public function testStringFieldToYaml()
    {
        $this->assertSame('foo', Yaml::dump((new StringField('foo'))->getValue()));
    }

    // DatetimeField tests

    public function testDatetimeFieldConstruct()
    {
        $this->assertEquals(new DateTime('2023-01-01'), (new DatetimeField('2023-01-01'))->getValue());
    }

    public function testDatetimeFieldGetValue()
    {
        $this->assertEquals(new DateTime('2023-01-01'), (new DatetimeField('2023-01-01'))->getValue());
    }

    public function testDatetimeFieldGetType()
    {
        $this->assertSame(DatetimeField::TYPE, DatetimeField::getType());
        $this->assertSame(PublicationFieldTypes::Datetime, DatetimeField::getType());
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
}

class TestValue extends PublicationFieldValue
{
    public const TYPE = PublicationFieldTypes::String;

    protected static function parseInput(string $input): string
    {
        return $input;
    }

    protected static function toYamlType(mixed $input): string
    {
        return $input;
    }
}
