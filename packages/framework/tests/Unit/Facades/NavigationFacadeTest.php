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
    public function testItem()
    {
        $item = Navigation::item('home', 'Home', 100);

        $this->assertSame([
            'destination' => 'home',
            'label' => 'Home',
            'priority' => 100,
            'attributes' => [],
        ], $item);
    }

    public function testItemWithOnlyDestination()
    {
        $item = Navigation::item('home');

        $this->assertSame([
            'destination' => 'home',
            'label' => null,
            'priority' => null,
            'attributes' => [],
        ], $item);
    }

    public function testItemWithUrl()
    {
        $item = Navigation::item('https://example.com', 'External', 200);

        $this->assertSame([
            'destination' => 'https://example.com',
            'label' => 'External',
            'priority' => 200,
            'attributes' => [],
        ], $item);
    }
}
