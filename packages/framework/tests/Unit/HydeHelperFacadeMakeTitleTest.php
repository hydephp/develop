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
}
