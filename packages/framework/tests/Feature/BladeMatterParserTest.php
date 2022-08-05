<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\BladeMatterParser;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\BladeMatterParser
 */
class BladeMatterParserTest extends TestCase
{
    public function test_line_matches_front_matter()
    {
        $this->assertTrue(BladeMatterParser::lineMatchesFrontMatter('@php($title = "BladeMatter Test")'));
        $this->assertFalse(BladeMatterParser::lineMatchesFrontMatter('foo bar'));
    }

    public function test_extract_key()
    {
        $this->assertSame('title', BladeMatterParser::extractKey('@php($title = "BladeMatter Test")'));
    }

    public function test_extract_value()
    {
        $this->assertSame('BladeMatter Test', BladeMatterParser::extractValue('@php($title = "BladeMatter Test")'));
    }

    public function test_normalize_value()
    {
        $this->assertSame('string', BladeMatterParser::normalizeValue('string'));
        $this->assertSame("string", BladeMatterParser::normalizeValue("string"));
        $this->assertSame(true, BladeMatterParser::normalizeValue('true'));
        $this->assertSame(false, BladeMatterParser::normalizeValue('false'));
        $this->assertSame(1, BladeMatterParser::normalizeValue('1'));
        $this->assertSame(0, BladeMatterParser::normalizeValue('0'));
        $this->assertSame(1.0, BladeMatterParser::normalizeValue('1.0'));
        $this->assertSame(0.0, BladeMatterParser::normalizeValue('0.0'));
        $this->assertSame(null, BladeMatterParser::normalizeValue('null'));
        $this->assertSame('["foo" => "bar"]', BladeMatterParser::normalizeValue('["foo" => "bar"]'));
    }
}
