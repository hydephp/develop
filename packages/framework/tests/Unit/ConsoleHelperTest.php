<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Console\Helpers\ConsoleHelper;
use Hyde\Testing\UnitTestCase;

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

    public function testCanMockWindowsOs()
    {
        $this->assertTrue(ConsoleHelper::canUseLaravelPrompts());

        ConsoleHelper::mockWindowsOs(true);

        $this->assertFalse(ConsoleHelper::canUseLaravelPrompts());
    }
}
