<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

class HydeHelperFacadeMakeTitleTest extends TestCase
{
    public function test_make_title_helper_parses_kebab_case_into_title()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('hello-world'));
    }

    public function test_make_title_helper_parses_snake_case_into_title()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('hello_world'));
    }

    public function test_make_title_helper_parses_camel_case_into_title()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('helloWorld'));
    }

    public function test_make_title_helper_parses_pascal_case_into_title()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('HelloWorld'));
    }

    public function test_make_title_helper_parses_title_case_into_title()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('Hello World'));
    }

    public function test_make_title_helper_parses_title_case_with_spaces_into_title()
    {
        $this->assertEquals('Hello World', Hyde::makeTitle('Hello World'));
    }

    public function test_make_title_helper_does_not_capitalize_auxiliary_words()
    {
        $this->assertEquals('The a an the in on by with of and or but',
            Hyde::makeTitle('the_a_an_the_in_on_by_with_of_and_or_but'));
    }
}
