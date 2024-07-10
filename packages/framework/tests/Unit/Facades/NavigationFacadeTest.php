<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Navigation;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Facades\Navigation
 */
class NavigationFacadeTest extends UnitTestCase
{
    public function testItem(): void
    {
        $item = Navigation::item('home', 'Home', 100);

        $this->assertSame([
            'destination' => 'home',
            'label' => 'Home',
            'priority' => 100,
        ], $item);
    }

    public function testItemWithOnlyDestination(): void
    {
        $item = Navigation::item('home');

        $this->assertSame([
            'destination' => 'home',
            'label' => null,
            'priority' => null,
        ], $item);
    }
}
