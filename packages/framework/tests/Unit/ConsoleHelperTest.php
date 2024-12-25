<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Console\Helpers\ConsoleHelper;
use Hyde\Testing\UnitTestCase;
use ReflectionFunction;
use ReflectionMethod;

/**
 * @covers \Hyde\Console\Helpers\ConsoleHelper
 */
class ConsoleHelperTest extends UnitTestCase
{
    protected function setUp(): void
    {
        ConsoleHelper::clearMocks();
    }

    protected function tearDown(): void
    {
        ConsoleHelper::clearMocks();
    }

    public function testClearMocksClearsAllMocks()
    {
        // Set up some mocks
        ConsoleHelper::mockWindowsOs(true);

        // Clear mocks
        ConsoleHelper::clearMocks();

        // Assert mocks are cleared by checking default behavior is restored
        $this->assertSame(windows_os(), ConsoleHelper::usesWindowsOs());
    }

    public function testUsesWindowsOsReturnsSystemValueByDefault()
    {
        $this->assertSame(windows_os(), ConsoleHelper::usesWindowsOs());
    }

    public function testUsesWindowsOsReturnsMockedValue()
    {
        ConsoleHelper::mockWindowsOs(true);
        $this->assertTrue(ConsoleHelper::usesWindowsOs());

        ConsoleHelper::mockWindowsOs(false);
        $this->assertFalse(ConsoleHelper::usesWindowsOs());
    }
}
