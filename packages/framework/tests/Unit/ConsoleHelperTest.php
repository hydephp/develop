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

    public function testMultiselectSignatureMatchesLaravelPrompts()
    {
        $reflectionLaravel = new ReflectionFunction('\Laravel\Prompts\multiselect');
        $reflectionHelper = new ReflectionMethod(ConsoleHelper::class, 'multiselect');

        $laravelParams = $reflectionLaravel->getParameters();
        $helperParams = $reflectionHelper->getParameters();

        // Test parameter count
        $this->assertCount(count($laravelParams), $helperParams, 'Parameter count mismatch');

        // Test each parameter
        foreach ($laravelParams as $index => $laravelParam) {
            $helperParam = $helperParams[$index];

            $this->assertSame(
                $laravelParam->getName(),
                $helperParam->getName(),
                "Parameter name mismatch at position {$index}"
            );

            $this->assertEquals(
                $laravelParam->getType(),
                $helperParam->getType(),
                "Parameter type mismatch for '{$laravelParam->getName()}'"
            );

            $this->assertSame(
                $laravelParam->isOptional(),
                $helperParam->isOptional(),
                "Parameter optionality mismatch for '{$laravelParam->getName()}'"
            );

            if ($laravelParam->isDefaultValueAvailable()) {
                $this->assertTrue(
                    $helperParam->isDefaultValueAvailable(),
                    "Default value availability mismatch for '{$laravelParam->getName()}'"
                );

                $this->assertEquals(
                    $laravelParam->getDefaultValue(),
                    $helperParam->getDefaultValue(),
                    "Default value mismatch for '{$laravelParam->getName()}'"
                );
            }
        }

        // Test return type
        $this->assertEquals(
            $reflectionLaravel->getReturnType(),
            $reflectionHelper->getReturnType(),
            'Return type mismatch'
        );
    }
}
