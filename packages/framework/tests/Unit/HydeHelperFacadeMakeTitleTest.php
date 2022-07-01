<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Helpers\HydeHelperFacade;
use Hyde\Testing\TestCase;

class HydeHelperFacadeMakeTitleTest extends TestCase
{
    public function test_make_title_helper_parses_kebab_case_into_title()
    {
        $this->assertEquals('Hello World', HydeHelperFacade::makeTitle('hello-world'));
    }

    public function test_make_title_helper_parses_snake_case_into_title()
    {
        $this->assertEquals('Hello World', HydeHelperFacade::makeTitle('hello_world'));
    }

    public function test_make_title_helper_parses_camel_case_into_title()
    {
        $this->assertEquals('Hello World', HydeHelperFacade::makeTitle('helloWorld'));
    }

    public function test_make_title_helper_parses_pascal_case_into_title()
    {
        $this->assertEquals('Hello World', HydeHelperFacade::makeTitle('HelloWorld'));
    }

    public function test_make_title_helper_parses_title_case_into_title()
    {
        $this->assertEquals('Hello World', HydeHelperFacade::makeTitle('Hello World'));
    }

    public function test_make_title_helper_parses_title_case_with_spaces_into_title()
    {
        $this->assertEquals('Hello World', HydeHelperFacade::makeTitle('Hello World'));
    }
}
