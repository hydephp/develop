<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;

class HydeHelperFacadeMakeTitleTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    public function testMakeTitleHelperParsesKebabCaseIntoTitle()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('hello-world'));
    }

    public function testMakeTitleHelperParsesSnakeCaseIntoTitle()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('hello_world'));
    }

    public function testMakeTitleHelperParsesCamelCaseIntoTitle()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('helloWorld'));
    }

    public function testMakeTitleHelperParsesPascalCaseIntoTitle()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('HelloWorld'));
    }

    public function testMakeTitleHelperParsesTitleCaseIntoTitle()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('Hello World'));
    }

    public function testMakeTitleHelperParsesTitleCaseWithSpacesIntoTitle()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('Hello World'));
    }

    public function testMakeTitleHelperDoesNotCapitalizeAuxiliaryWords()
    {
        $this->assertEquals('The a an the in on by with of and or but',
            Hyde::makeTitle('the_a_an_the_in_on_by_with_of_and_or_but'));
    }
}
