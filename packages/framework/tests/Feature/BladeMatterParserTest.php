<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\BladeMatterParser;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\BladeMatterParser
 */
class BladeMatterParserTest extends TestCase
{
    public function test_can_parse_front_matter()
    {
        $parser = new BladeMatterParser('@php($foo = "bar")');
        $parser->parse();
        $this->assertEquals(['foo' => 'bar'], $parser->get());
    }

    public function test_parse_string_helper_method()
    {
        $this->assertSame(
            (new BladeMatterParser('foo'))->parse()->get(),
            BladeMatterParser::parseString('foo')
        );
    }

    public function test_parse_file_helper_method()
    {
        $this->file('foo', 'foo');
        $this->assertSame(
            (new BladeMatterParser('foo'))->parse()->get(),
            BladeMatterParser::parseFile('foo')
        );
    }

    public function test_can_parse_multiple_front_matter_lines()
    {
        $document = <<<'BLADE'
        @php($foo = 'bar')
        @php($bar = 'baz')
        @php($baz = 'qux')
        BLADE;
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux'], BladeMatterParser::parseString($document));
    }

    public function test_can_parse_front_matter_with_various_formats()
    {
        $matrix = [
            '@php($foo = "bar")' => ['foo' => 'bar'],
            '@php($foo = \'bar\')' => ['foo' => 'bar'],
            '@php($foo="bar")' => ['foo' => 'bar'],
            '@php($foo  =  "bar"  )  ' => ['foo' => 'bar'],
        ];

        foreach ($matrix as $input => $expected) {
            $this->assertEquals($expected, BladeMatterParser::parseString($input));
        }
    }

    public function test_can_parse_front_matter_with_array()
    {
        $document = "@php(\$foo = ['bar' => 'baz'])";
        $this->assertEquals(['foo' => ['bar' => 'baz']], BladeMatterParser::parseString($document));
    }

    public function test_line_matches_front_matter()
    {
        $this->assertTrue(BladeMatterParser::lineMatchesFrontMatter('@php($foo = "bar")'));
        $this->assertFalse(BladeMatterParser::lineMatchesFrontMatter('foo bar'));
    }

    public function test_directive_cannot_have_leading_whitespace()
    {
        $this->assertFalse(BladeMatterParser::lineMatchesFrontMatter(' @php($foo = "bar")'));
    }

    public function test_directive_signature_cannot_contain_whitespace()
    {
        $this->assertFalse(BladeMatterParser::lineMatchesFrontMatter('@php( $foo = "bar")'));
        $this->assertFalse(BladeMatterParser::lineMatchesFrontMatter('@ php($foo = "bar")'));
        $this->assertFalse(BladeMatterParser::lineMatchesFrontMatter('@ php ($foo = "bar")'));
    }

    public function test_extract_key()
    {
        $this->assertSame('foo', BladeMatterParser::extractKey('@php($foo = "bar")'));
    }

    public function test_extract_value()
    {
        $this->assertSame('bar', BladeMatterParser::extractValue('@php($foo = "bar")'));
    }

    public function test_normalize_value()
    {
        $this->assertSame('string', BladeMatterParser::normalizeValue('string'));
        $this->assertSame('string', BladeMatterParser::normalizeValue('string'));
        $this->assertSame(true, BladeMatterParser::normalizeValue('true'));
        $this->assertSame(false, BladeMatterParser::normalizeValue('false'));
        $this->assertSame(1, BladeMatterParser::normalizeValue('1'));
        $this->assertSame(0, BladeMatterParser::normalizeValue('0'));
        $this->assertSame(1.0, BladeMatterParser::normalizeValue('1.0'));
        $this->assertSame(0.0, BladeMatterParser::normalizeValue('0.0'));
        $this->assertSame(null, BladeMatterParser::normalizeValue('null'));
        $this->assertSame(['foo' => 'bar'], BladeMatterParser::normalizeValue('["foo" => "bar"]'));
        $this->assertSame(['foo' => 'bar'], BladeMatterParser::normalizeValue("['foo' => 'bar']"));
    }

    public function test_parse_array_string()
    {
        $this->assertSame(['foo' => 'bar'], BladeMatterParser::parseArrayString('["foo" => "bar"]'));
        $this->assertSame(['foo' => 'bar'], BladeMatterParser::parseArrayString('["foo" => "bar"]'));
        $this->assertSame(['foo' => 'bar'], BladeMatterParser::parseArrayString("['foo' => 'bar']"));

        $this->assertSame(['foo' => 'bar', 'bar' => 'baz'], BladeMatterParser::parseArrayString('["foo" => "bar", "bar" => "baz"]'));
        $this->assertSame(['foo' => 'true'], BladeMatterParser::parseArrayString('["foo" => "true"]'));
        $this->assertSame(['foo' => true], BladeMatterParser::parseArrayString('["foo" => true]'));
        $this->assertSame(['foo' => '1'], BladeMatterParser::parseArrayString('["foo" => "1"]'));
        $this->assertSame(['foo' => 1], BladeMatterParser::parseArrayString('["foo" => 1]'));
    }

    public function test_parse_invalid_array_string()
    {
        $this->expectException(\RuntimeException::class);
        BladeMatterParser::parseArrayString('foo');
    }

    public function test_parse_multidimensional_array_string()
    {
        $this->expectException(\RuntimeException::class);
        BladeMatterParser::parseArrayString('["foo" => ["bar" => "baz"]]');
    }
}
