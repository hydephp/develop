<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue
 */
class PublicationFieldValueObjectsTest extends TestCase
{
    public function testConstruct()
    {
        $value = new TestValue('test');
        $this->assertSame('test', $value->getValue());
    }

    public function testGetValue()
    {
        $value = new TestValue('test');
        $this->assertSame('test', $value->getValue());
    }

    public function testParseInput()
    {
        $value = TestValue::parseInput('test');
        $this->assertSame('test', $value);
    }

    public function testToYamlType()
    {
        $value = TestValue::toYamlType('test');
        $this->assertSame('test', $value);
    }
}

class TestValue extends PublicationFieldValue
{
    public static function parseInput(string $input): string
    {
        return $input;
    }

    public static function toYamlType(string $input): string
    {
        return $input;
    }
}
