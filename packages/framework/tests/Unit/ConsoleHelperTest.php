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

    public function testClearMocksClearsAllMocks()
    {
        // Set up some mocks
        ConsoleHelper::mockWindowsOs(true);
        ConsoleHelper::mockMultiselect(['option1']);

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

    public function testMultiselectReturnsMockedValues()
    {
        $mockedReturn = ['option1', 'option2'];
        ConsoleHelper::mockMultiselect($mockedReturn);

        $result = ConsoleHelper::multiselect(
            'Select options',
            ['option1', 'option2', 'option3'],
            []
        );

        $this->assertSame($mockedReturn, $result);
    }

    public function testMultiselectAssertionCallbackIsExecuted()
    {
        $wasCalled = false;
        $expectedLabel = 'Select options';
        $expectedOptions = ['option1', 'option2'];
        $expectedDefault = ['option1'];

        ConsoleHelper::mockMultiselect(
            ['option1'],
            function ($label, $options, $default) use (
                $expectedLabel,
                $expectedOptions,
                $expectedDefault,
                &$wasCalled
            ) {
                $this->assertSame($expectedLabel, $label);
                $this->assertSame($expectedOptions, $options);
                $this->assertSame($expectedDefault, $default);
                $wasCalled = true;
            }
        );

        ConsoleHelper::multiselect($expectedLabel, $expectedOptions, $expectedDefault);
        $this->assertTrue($wasCalled);
    }
}
