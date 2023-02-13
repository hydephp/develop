<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\BladeMatterParser;
use Hyde\Testing\TestCase;
use RuntimeException;

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
        $this->assertTrue(ParserTestClass::lineMatchesFrontMatter('@php($foo = "bar")'));
        $this->assertFalse(ParserTestClass::lineMatchesFrontMatter('foo bar'));
    }

    public function test_directive_cannot_have_leading_whitespace()
    {
        $this->assertFalse(ParserTestClass::lineMatchesFrontMatter(' @php($foo = "bar")'));
    }

    public function test_directive_signature_cannot_contain_whitespace()
    {
        $this->assertFalse(ParserTestClass::lineMatchesFrontMatter('@php( $foo = "bar")'));
        $this->assertFalse(ParserTestClass::lineMatchesFrontMatter('@ php($foo = "bar")'));
        $this->assertFalse(ParserTestClass::lineMatchesFrontMatter('@ php ($foo = "bar")'));
    }

    public function test_extract_key()
    {
        $this->assertSame('foo', ParserTestClass::extractKey('@php($foo = "bar")'));
    }

    public function test_extract_value()
    {
        $this->assertSame('bar', ParserTestClass::extractValue('@php($foo = "bar")'));
    }

    public function test_get_value_with_type()
    {
        $this->assertSame('string', ParserTestClass::getValueWithType('string'));
        $this->assertSame('string', ParserTestClass::getValueWithType('string'));
        $this->assertSame(true, ParserTestClass::getValueWithType('true'));
        $this->assertSame(false, ParserTestClass::getValueWithType('false'));
        $this->assertSame(1, ParserTestClass::getValueWithType('1'));
        $this->assertSame(0, ParserTestClass::getValueWithType('0'));
        $this->assertSame(1.0, ParserTestClass::getValueWithType('1.0'));
        $this->assertSame(0.0, ParserTestClass::getValueWithType('0.0'));
        $this->assertSame(null, ParserTestClass::getValueWithType('null'));
        $this->assertSame(['foo' => 'bar'], ParserTestClass::getValueWithType('["foo" => "bar"]'));
        $this->assertSame(['foo' => 'bar'], ParserTestClass::getValueWithType("['foo' => 'bar']"));
    }

    public function test_parse_array_string()
    {
        $this->assertSame(['foo' => 'bar'], ParserTestClass::parseArrayString('["foo" => "bar"]'));
        $this->assertSame(['foo' => 'bar'], ParserTestClass::parseArrayString('["foo" => "bar"]'));
        $this->assertSame(['foo' => 'bar'], ParserTestClass::parseArrayString("['foo' => 'bar']"));

        $this->assertSame(['foo' => 'bar', 'bar' => 'baz'], ParserTestClass::parseArrayString('["foo" => "bar", "bar" => "baz"]'));
        $this->assertSame(['foo' => 'true'], ParserTestClass::parseArrayString('["foo" => "true"]'));
        $this->assertSame(['foo' => true], ParserTestClass::parseArrayString('["foo" => true]'));
        $this->assertSame(['foo' => '1'], ParserTestClass::parseArrayString('["foo" => "1"]'));
        $this->assertSame(['foo' => 1], ParserTestClass::parseArrayString('["foo" => 1]'));
    }

    public function test_parse_invalid_array_string()
    {
        $this->expectException(RuntimeException::class);
        ParserTestClass::parseArrayString('foo');
    }

    public function test_parse_multidimensional_array_string()
    {
        $this->expectException(RuntimeException::class);
        ParserTestClass::parseArrayString('["foo" => ["bar" => "baz"]]');
    }
}

class ParserTestClass extends BladeMatterParser
{
    public static function lineMatchesFrontMatter(string $line): bool
    {
        return parent::lineMatchesFrontMatter($line);
    }

    public static function extractKey(string $line): string
    {
        return parent::extractKey($line);
    }

    public static function extractValue(string $line): string
    {
        return parent::extractValue($line);
    }

    public static function getValueWithType(string $value): mixed
    {
        return parent::getValueWithType($value);
    }

    public static function parseArrayString(string $string): array
    {
        return parent::parseArrayString($string);
    }

    public static function isValueArrayString(string $string): bool
    {
        return parent::isValueArrayString($string);
    }
}
