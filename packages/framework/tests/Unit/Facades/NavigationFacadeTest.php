<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Navigation;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Facades\Navigation::class)]
class NavigationFacadeTest extends UnitTestCase
{
    public function testItemWithoutLabel()
    {
        $item = Navigation::item('foo');

        $this->assertSame([
            'destination' => 'foo',
            'label' => null,
            'priority' => null,
            'attributes' => [],
        ], $item);
    }

    public function testItemWithLabel()
    {
        $item = Navigation::item('foo', 'Foo');

        $this->assertSame([
            'destination' => 'foo',
            'label' => 'Foo',
            'priority' => null,
            'attributes' => [],
        ], $item);
    }

    public function testItemWithPriority()
    {
        $item = Navigation::item('foo', 'Foo', 100);

        $this->assertSame([
            'destination' => 'foo',
            'label' => 'Foo',
            'priority' => 100,
            'attributes' => [],
        ], $item);
    }

    public function testItemWithUrl()
    {
        $item = Navigation::item('https://example.com');

        $this->assertSame([
            'destination' => 'https://example.com',
            'label' => null,
            'priority' => null,
            'attributes' => [],
        ], $item);
    }
}
