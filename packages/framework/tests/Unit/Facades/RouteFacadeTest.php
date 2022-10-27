<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Route;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Route
 */
class RouteFacadeTest extends TestCase
{
    public function test_route_facade_can_call_methods_on_the_route_model()
    {
        $this->assertEquals(Route::all(), \Hyde\Support\Models\Route::all());
    }
}
