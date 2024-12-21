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
}
