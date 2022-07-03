<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

class HelpersTest extends TestCase
{
    /** @covers ::hyde */
    public function test_hyde_function_exists()
    {
        $this->assertTrue(function_exists('hyde'));
    }

    /** @covers ::hyde */
    public function test_hyde_function_returns_hyde_class()
    {
        $this->assertInstanceOf(Hyde::class, hyde());
    }

    /** @covers ::hyde */
    public function test_can_call_methods_on_returned_hyde_class()
    {
        $this->assertSame(Hyde::path(), hyde()->path());
    }
}
