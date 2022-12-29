<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\PublicationFieldValueTests;

use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue
 */
abstract class BaseFieldValueTest extends TestCase
{
    /** @var class-string|\Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue */
    protected static string|PublicationFieldValue $fieldClass = PublicationFieldValue::class;

    public function testConstruct()
    {
        $value = new static::$fieldClass('foo');
        $this->assertSame('foo', $value->getValue());
    }

    public function testGetValue()
    {
        $value = new static::$fieldClass('foo');
        $this->assertSame('foo', $value->getValue());
    }

    public function testGetType()
    {
        $this->assertSame(static::$fieldClass::TYPE, static::$fieldClass::getType());
        $this->assertSame(PublicationFieldTypes::String, static::$fieldClass::getType());
    }

    public function testParseInput()
    {
        $value = static::$fieldClass::parseInput('foo');
        $this->assertSame('foo', $value);
    }

    public function testToYamlType()
    {
        $value = static::$fieldClass::toYamlType('foo');
        $this->assertSame('foo', $value);
    }
}
