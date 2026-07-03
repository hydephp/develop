<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Support\ConfigOverrideValueParser;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\ConfigOverrideValueParser::class)]
class ConfigOverrideValueParserTest extends UnitTestCase
{
    public function testParsesTrue()
    {
        $this->assertTrue(ConfigOverrideValueParser::parse('true'));
        $this->assertTrue(ConfigOverrideValueParser::parse('TRUE'));
    }

    public function testParsesFalse()
    {
        $this->assertFalse(ConfigOverrideValueParser::parse('false'));
        $this->assertFalse(ConfigOverrideValueParser::parse('FALSE'));
    }

    public function testParsesNull()
    {
        $this->assertNull(ConfigOverrideValueParser::parse('null'));
        $this->assertNull(ConfigOverrideValueParser::parse('NULL'));
    }

    public function testParsesIntegers()
    {
        $this->assertSame(1234, ConfigOverrideValueParser::parse('1234'));
        $this->assertSame(-12, ConfigOverrideValueParser::parse('-12'));
    }

    public function testParsesFloats()
    {
        $this->assertSame(4.5, ConfigOverrideValueParser::parse('4.5'));
        $this->assertSame(-4.5, ConfigOverrideValueParser::parse('-4.5'));
    }

    public function testParsesStrings()
    {
        $this->assertSame('foo', ConfigOverrideValueParser::parse('foo'));
        $this->assertSame('example.com', ConfigOverrideValueParser::parse('example.com'));
    }
}
