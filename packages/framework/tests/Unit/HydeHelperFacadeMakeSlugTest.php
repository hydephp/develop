<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;

class HydeHelperFacadeMakeSlugTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    public function testMakeSlugHelperConvertsTitleCaseToSlug()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('Hello World'));
    }

    public function testMakeSlugHelperConvertsKebabCaseToSlug()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('hello-world'));
    }

    public function testMakeSlugHelperConvertsSnakeCaseToSlug()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('hello_world'));
    }

    public function testMakeSlugHelperConvertsCamelCaseToSlug()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('helloWorld'));
    }

    public function testMakeSlugHelperConvertsPascalCaseToSlug()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('HelloWorld'));
    }

    public function testMakeSlugHelperHandlesMultipleSpaces()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('Hello    World'));
    }

    public function testMakeSlugHelperHandlesSpecialCharacters()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('Hello & World!'));
    }

    public function testMakeSlugHelperConvertsUppercaseToLowercase()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('HELLO WORLD'));
        $this->assertSame('hello-world', Hyde::makeSlug('HELLO_WORLD'));
    }

    public function testMakeSlugHelperHandlesNumbers()
    {
        $this->assertSame('hello-world-123', Hyde::makeSlug('Hello World 123'));
    }

    public function testMakeSlugHelperTransliteratesChineseCharacters()
    {
        $this->assertSame('ni-hao-shi-jie', Hyde::makeSlug('你好世界'));
    }

    public function testMakeSlugHelperTransliteratesJapaneseCharacters()
    {
        $this->assertSame('konnichihashi-jie', Hyde::makeSlug('こんにちは世界'));
    }

    public function testMakeSlugHelperTransliteratesKoreanCharacters()
    {
        $this->assertSame('annyeongsegye', Hyde::makeSlug('안녕세계'));
    }

    public function testMakeSlugHelperTransliteratesArabicCharacters()
    {
        $this->assertSame('mrhb-bllm', Hyde::makeSlug('مرحبا بالعالم'));
    }

    public function testMakeSlugHelperTransliteratesRussianCharacters()
    {
        $this->assertSame('privet-mir', Hyde::makeSlug('Привет мир'));
    }

    public function testMakeSlugHelperTransliteratesAccentedLatinCharacters()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('hèllô wórld'));
        $this->assertSame('uber-strasse', Hyde::makeSlug('über straße'));
    }

    public function testMakeSlugHelperHandlesMixedScripts()
    {
        $this->assertSame('hello-ni-hao-world', Hyde::makeSlug('Hello 你好 World'));
        $this->assertSame('privet-world', Hyde::makeSlug('Привет World'));
    }

    public function testMakeSlugHelperHandlesEmojis()
    {
        $this->assertSame('hello-world', Hyde::makeSlug('Hello 👋 World'));
        $this->assertSame('world', Hyde::makeSlug('😊 World'));
    }

    public function testMakeSlugHelperHandlesComplexMixedInput()
    {
        $this->assertSame(
            'hello-ni-hao-privet-bonjour-world-123',
            Hyde::makeSlug('Hello 你好 Привет Bonjóur World 123!')
        );
    }

    public function testMakeSlugHelperHandlesEdgeCases()
    {
        $this->assertSame('', Hyde::makeSlug(''));
        $this->assertSame('at', Hyde::makeSlug('!@#$%^&*()'));
        $this->assertSame('', Hyde::makeSlug('...   ...'));
        $this->assertSame('multiple-dashes', Hyde::makeSlug('multiple---dashes'));
    }

    public function testMakeSlugHelperPreservesValidCharacters()
    {
        $this->assertSame('abc-123', Hyde::makeSlug('abc-123'));
        $this->assertSame('test-slug', Hyde::makeSlug('test-slug'));
    }

    public function testMakeSlugHelperHandlesWhitespace()
    {
        $this->assertSame('trim-spaces', Hyde::makeSlug('   trim spaces   '));
        $this->assertSame('newline-test', Hyde::makeSlug("newline\ntest"));
        $this->assertSame('tab-test', Hyde::makeSlug("tab\ttest"));
    }
}
